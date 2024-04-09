<script>
    import Select from "../../../components/select.svelte";
    import Icons from "../../../components/icons.svelte";
    import Layout from "../../../layouts/Layout.svelte";
    import { onMount } from "svelte";
    import { page, inertia } from "@inertiajs/svelte";
    import { router } from "../../../../util";
    import Table from "../../../components/table.svelte";
    import Button from "../../../components/button.svelte";


    let assessmentId = $page.props.assessmentId;
    let assessmentTitle = $page.props.assessmentTitle;
    
    let selectedSubject;
    
    let questionBanks = [];
    let subjects = [];

    let headings = ['S|N', 'SUBJECT', 'TOTAL QUESTIONS', 'LEVELS', 'LECTURER', 'ACTIONS'];

    onMount(() => {

        router.getWithToken('/api/assessment/question-banks/' + assessmentId, {
            onSuccess : (res) => {
                questionBanks = res.data
            }
        });

        getSubjects();
        
    });

    const getSubjects = () => {

        router.getWithToken('/api/question-bank-subjects/' + assessmentId, {
            onSuccess : (res) => {
                subjects = res.data.flatMap((subject) => [ { placeholder: subject.subjectName, value: subject.subjectCode } ])
            }
        });
    }

    const viewAssessmentQuestions = (subjectId) => {
     
    }

    

    $: getQuestionBanks = () => {

        return questionBanks.map((questionBank) => {

            if( ! selectedSubject ){

                questionBank.isVisible = true;

                return questionBank;
            }

            if( selectedSubject === questionBank.subjectCode ){

                questionBank.isVisible = true;

            }else{

                questionBank.isVisible = false;
            }

            return questionBank;

        })
    }
    
</script>

<Layout>
   <div class="container">
        <div class="my-28">
            <div class="flex items-center justify-between">
                <div class="flex space-x-3 items-center">
                    <Icons icon="chart" className="h-6 w-6" />
                    <span class="mx-2 text-lg font-medium text-gray-700 uppercase">Question Bank</span>
                    <Icons icon="chevron_right" className="h-4 w-4 fill-gray-800" />
                    <span class="mx-2 text-lg font-medium text-gray-800 uppercase">{ assessmentTitle }</span>
                </div>

                <Button on:click={ () => router.navigateTo(`/assessments/termly/question_bank/create/${assessmentId}`)} buttonText="Create New Question Bank" className="max-w-min min-w-max text-sm" />
            </div>
            <div class="flex flex-col justify-start min-h-[24rem] w-full bg-white rounded-lg mt-10 border border-gray-100 overflow-hidden p-6">
                <div class="w-60 mb-6">
                    <Select on:selected={ (e) => selectedSubject = e.detail.value } on:deselected={ (e) => selectedSubject = null } options={ subjects } placeholder="Select Subject" className="text-sm" />
                </div>
                <Table { headings }>
                    { #each getQuestionBanks() as questionBank, index }
                        <tr>
                            { #if questionBank.isVisible }
                                <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300 whitespace-nowrap">{ index + 1  }</td>
                                <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300 whitespace-nowrap">{ questionBank.subject  }</td>
                                <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300 whitespace-nowrap">{ questionBank.totalQuestions  } Questions</td>
                                <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300 whitespace-nowrap">
                                    { #each questionBank.classes as grade }
                                        <p>{ grade }</p>
                                    {/each}
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300 whitespace-nowrap">{ questionBank.teacher  }</td>
                                <td class="px-4 py-4 text-sm text-gray-800 dark:text-gray-300 whitespace-nowrap">
                                    <a use:inertia href={ `/assessment/questions/question-bank/${ questionBank.questionBankId }` } class="text-white bg-gray-800 rounded-lg text-sm p-2.5">View Questions</a>
                                    <a use:inertia href={ `/assessments/termly/question_bank/edit/${ questionBank.questionBankId }` } class="text-white bg-blue-500 rounded-lg text-sm p-2.5">Edit</a>
                                </td>
                            { /if }
                        </tr>
                    {/each}
                </Table>
            </div>
        </div>
   </div>
</Layout>