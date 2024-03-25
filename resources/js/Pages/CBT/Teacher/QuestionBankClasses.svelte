<script>
    import Button from "../../components/button.svelte";
    import Icons from "../../components/icons.svelte";
    import Layout from "./components/layout.svelte";

    import { page } from "@inertiajs/svelte";
    import { router } from "../../../util";
    import { onMount } from "svelte";


    let classes = [];
    let selectedClasses = []

    let questionBankId = $page.props.questionBankId;

    let selectAll = false

    onMount(() => {

        router.getWithToken('/api/question-bank/assessment-classes/' + questionBankId, {
            onSuccess : (res) => {
                classes = res.data;
            }
        })

        router.getWithToken(`/api/question-bank/classes/${questionBankId}`, {
            onSuccess : (res) => {
                selectedClasses = res.data;
            }
        })
    });

    $: isSelected = ( gradeId ) => {
        return selectedClasses.some((selected) => selected.class_code == gradeId);
    }

    const selectClass = ( grade ) => {

        if(isSelected(grade.class_code)){
            selectedClasses = selectedClasses.filter((selected) => selected.class_code != grade.class_code);
            return ;
        }

        selectedClasses.push(grade);
        selectedClasses = selectedClasses;
    }

    
    const selectAllClasses = () => {

        if( selectAll ){

            selectAll = false;
            selectedClasses = []

        }else{

            selectAll = true;
            selectedClasses = classes
        }
    }

    const addClassesToQuestionBank = () => {

        let classData = selectedClasses.flatMap((grade) => [ grade.class_code ]);

        router.postWithToken('/api/question-bank/create/classes', { questionBankId, classes: classData }, {
            onSuccess : (res) => {
                router.navigateTo(`/teacher/create-question-bank/sections/${questionBankId}`);
            }
        });
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
                        <div class="text-lg font-semibold min-w-max text-gray-700">Add Classes</div>
                        <div class="text-gray-400 text-sm">Please select relevant details below</div>
                    </div>
                </div>
        
                <div class="mt-12 space-y-6 w-full">
                    <div class="flex w-full text-sm max-w-4xl">
                        <span class="flex-shrink-0 w-[18rem] text-gray-700">Select classes question bank belong to</span>
                        <div class="w-full space-y-2">
                            <button  on:click={ selectAllClasses } class={`flex items-center shrink-0 justify-center h-12 w-12 border-2 ${ selectAll ? 'border-green-700' : 'border-gray-300' } rounded-lg`}>
                                <Icons icon="check" className={`${ selectAll ? "stroke-green-700" : "stroke-gray-300" }`} />
                            </button>
                            { #each classes as grade }
                                <div class="flex space-x-2 items-center w-full">
                                    <button  on:click={ () => selectClass(grade) } class={`flex items-center shrink-0 justify-center h-12 w-12 border-2 ${ isSelected(grade.class_code) ? 'border-green-700' : 'border-gray-300' } rounded-lg`}>
                                        <Icons icon="check" className={`${ isSelected(grade.class_code) ? "stroke-green-700" : "stroke-gray-300" }`} />
                                    </button>
                                    <div class="w-full">
                                        <button type="button" class={`flex p-3 border-2 ${ isSelected(grade.class_code) ? 'border-green-700' : 'border-gray-300' }  w-full rounded-lg items-center justify-between space-x-2`}>
                                            <span class={`${ isSelected(grade.class_code) ? "text-green-700" : "text-gray-400" }`} >{ grade.class_name }</span>
                                        </button>
                                    </div>
                                </div>
                            {/each}
                        </div>
                    </div>
                </div>

                <div class="flex w-full mt-10 justify-end">
                    <div class="w-40">
                        <Button on:click={ addClassesToQuestionBank } buttonText="Continue" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</Layout>