<script>
    import { onMount } from "svelte";
    import Icons from "../../components/icons.svelte";
    import Select from "../../components/select.svelte";
    import Layout from "./components/layout.svelte";
    import Button from "../../components/button.svelte";
    import { router } from "../../../util";

    let assessments = [];
    let subjects = [];

    let assessment;
    let subject;

    onMount(() => {
         router.getWithToken('/api/published-assessments', {
            onSuccess: (res) => {
                assessments = res.data.flatMap((assessment) => [ { placeholder :  assessment.title , value: assessment.assessmentId }]);
            }
         });
    });

    const getSubjects = (assessmentId) => {

        router.getWithToken(`/api/question-bank/subjects/${assessmentId}`, {
            onSuccess : (res) => {
                subjects = res.data.flatMap((subject) => [{ placeholder : subject.subjectName, value: subject.subjectId } ]);
            }
        })
    }

    const questionBankClasses = () => {

        router.postWithToken('/api/question-bank/create', { assessmentId: assessment.value, subjectId: subject.value }, {
            onSuccess: (res) => {
                router.navigateTo(`/teacher/create-question-bank/classes/${res.data.questionBankId}`);
            }
        })
        
    }
</script>

<Layout>
    <div class="mt-20 flex items-center justify-between border-b bg-white">
        <div class="flex space-x-3 items-center py-4 px-8">
            <Icons icon="chart" className="h-5 w-5" />
            <span class="mx-2 text-sm font-medium text-gray-700">Questions </span>
            <Icons icon="chevron_right" className="h-4 w-4 fill-gray-800" />
            <span class="text-sm font-medium text-gray-700">Create</span>
        </div>
    </div>
    <div class="w-full mx-auto h-full"> 
        <div class="container max-w-4xl my-12 space-x-10">
            <div class="border bg-white p-8 rounded-lg max-w-8xl">
                <div class="flex items-center justify-between my-2 border-b pb-8">
                    <div>
                        <div class="text-lg font-semibold min-w-max text-gray-700">Create Question Bank</div>
                        <div class="text-gray-400 text-sm">Please select relevant details below</div>
                    </div>
                </div>
        
                <div class="mt-12 space-y-6 w-full">
                    <div class="flex w-full items-center text-sm max-w-4xl">
                        <div class="flex-shrink-0 w-[18rem] text-gray-700 font-semibold">Select Assessment</div>
                        <div class="w-full">
                            <Select on:selected={ (e) => getSubjects(e.detail.value) } on:deselected={ (e) => subjects = [] } options={ assessments }  bind:this={ assessment } placeholder={ "Select an Assessment"} className="text-sm py-2.5"  />
                        </div>
                    </div>
                    <div class="flex w-full items-center text-sm max-w-4xl">
                        <div class="flex-shrink-0 w-[18rem] text-gray-700 font-semibold">Select Subject</div>
                        <div class="w-full">
                            <Select options={ subjects }  bind:this={ subject } placeholder={ "Select Subject"} className="text-sm py-2.5"  />
                        </div>
                    </div>
                </div>

                <div class="flex w-full mt-10 justify-end">
                    <div class="w-40">
                        <Button on:click={ questionBankClasses } buttonText="Continue" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</Layout>