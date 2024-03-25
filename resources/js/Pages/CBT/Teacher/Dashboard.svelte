<script>
    import { onMount } from "svelte";
    import DataTable from "../../components/data_table.svelte";
    import Dropdown from "../../components/dropdown.svelte";
    import Layout from "./components/layout.svelte";
    import { router } from "../../../util";
    import { inertia } from "@inertiajs/svelte"
    import Button from "../../components/button.svelte";
    import Select from "../../components/select.svelte";


    let headings = ['S|N', 'Subject', 'Total Questions', 'Total Sections', 'Classes', 'Action'];

    let classes = [];
    let subjects = [];
    let assessments = [];

    let questionBanks = [];

    let selectedAssessment;
    let selectedSubject;

    onMount(() => {
        router.getWithToken('/api/get-subjects/', {
            onSuccess : (res) => {
                subjects = res.data.flatMap((subject) => [{ placeholder : subject.subjectName, value: subject.subjectId } ]);
            }
        })

        router.getWithToken('/api/published-assessments', {
            onSuccess: (res) => {
                assessments = res.data.flatMap((assessment) => [ { placeholder :  assessment.title , value: assessment.assessmentId }]);
            }
         })
    })

    const goToCreateQuestionBank = () => {
        router.navigateTo(`/teacher/create-question-bank`);
    }

    const getQuestionBanks = () => {

        router.getWithToken(`/api/question-banks?assessmentId=${selectedAssessment}&subjectId=${selectedSubject}`, {
            onSuccess : (res) => {
                questionBanks = res.data;
            }
        })
    }

</script>

<Layout>
    <div class="container">
        <div class="my-28">
           
            <div class="flex justify-between">
                <h3 class="text-lg font-semibold">Question Bank</h3>
                <Button on:click={ goToCreateQuestionBank } buttonText="New Question Bank" className="max-w-min min-w-max bg-green-600 focus:outline-none hover:bg-green-700 focus:bg-green-600 focus:ring-green-200" />
            </div>
         
            <div class="flex flex-col border border-gray-300 rounded-lg w-full h-full bg-white mt-8 pt-4 pb-8 px-4">
                <span class="text-base text-gray-700 font-semibold">Filters</span>
                <div class="flex items-center mt-3 space-x-3 w-full">
                    <Select on:selected={ (e) => selectedSubject = e.detail.value } on:deselected={ (e) => selectedSubject = null } options={ subjects }  optionsStyling="text-sm text-gray-800" placeholder="Select Subject" className="w-full text-sm text-gray-400 py-2.5" />
                    <Select on:selected={ (e) => selectedAssessment = e.detail.value } on:deselected={ (e) => selectedAssessment = null } options={ assessments }  optionsStyling="text-sm text-gray-800" placeholder="Select Assessment" className="w-full text-sm text-gray-400 py-2.5" />
                    <Button on:click={ getQuestionBanks }  buttonText="Search" className="w-40 text-sm py-3" />
                    <Button on:click  buttonText="Reset" className="w-40 text-sm py-3" />
                </div>
            </div>
            
            <DataTable { headings } >
                { #each questionBanks as questionBank, index }
                    <tr>
                        <td class="px-4 py-4 text-sm text-gray-800 dark:text-gray-300 whitespace-nowrap">{ index + 1  }</td>
                        <td class="px-4 py-4 text-sm text-gray-800 dark:text-gray-300 whitespace-nowrap">{ questionBank.subject }</td>
                        <td class="px-4 py-4 text-sm text-gray-800 dark:text-gray-300 whitespace-nowrap">{ questionBank.totalQuestions } Questions</td>
                        <td class="px-4 py-4 text-sm text-gray-800 dark:text-gray-300 whitespace-nowrap">{ questionBank.totalSections } Sections</td>
                        <td class="px-4 py-4 text-sm text-gray-800 dark:text-gray-300 whitespace-nowrap">
                            <div class="flex flex-col h-full">
                                { #each questionBank.classes as grade}
                                    <span>{ grade }</span>
                                {/each}
                            </div>
                        </td>
                        <td class="px-4 py-4 text-sm text-gray-800 dark:text-gray-300 whitespace-nowrap">
                            <div class="w-40">
                                <Dropdown arrowColor="fill-gray-600" placeholder="Actions" className="bg-white border  border-gray-300 text-gray-600">
                                    <a use:inertia href={`/teacher/questions/${questionBank.questionBankId}`}  class="hover:bg-gray-100 p-3 text-sm rounded transition text-left">View Questions</a>
                                    <a use:inertia href={`/teacher/create-question-bank/sections/${questionBank.questionBankId}`} class="hover:bg-gray-100 p-3 text-sm rounded transition text-left">Edit Sections</a>
                                    <a use:inertia href={`/teacher/create-question-bank/classes/${questionBank.questionBankId}`} class="hover:bg-gray-100 p-3 text-sm rounded transition text-left">Edit Classes</a>
                                    <button class="hover:bg-gray-100 p-3 text-sm rounded transition text-left">Delete</button>
                                </Dropdown>
                            </div>
                        </td>
                    </tr>
                { /each }
            </DataTable>
        </div>
    </div>
    
</Layout>