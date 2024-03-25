<script>
    import { onMount } from "svelte";
    import Button from "../../components/button.svelte";
    import Icons from "../../components/icons.svelte";
    import { router } from "../../../util";
    import { page } from "@inertiajs/svelte";

    let classes = [];
    let selectedClasses = []
    let assessmentId = $page.props.assessmentId;
    let selectAll = false

    let disabled = false;

    onMount(() => {

        console.log(assessmentId);

        router.getWithToken('/api/classes', {
            onSuccess : (res) => {
                classes = res.data
            }
        });

        router.getWithToken('/api/assessment-classes/' + assessmentId, {
            onSuccess : (res) => {
                selectedClasses = res.data
            }
        });
    })

    $: isSelected = (gradeId) => {
        return selectedClasses.some((selected) => selected.class_code == gradeId);
    }

    const selectClass = (grade) => {

        if(isSelected(grade.class_code)){
            selectedClasses = selectedClasses.filter((selected) => selected.class_code != grade.class_code);
            return ;
        }

        selectedClasses.push(grade);
        selectedClasses = selectedClasses;
    }

    const assignClasses = () => {

        disabled = true;

        router.postWithToken('/api/assessment/assign-classes', { assessmentId, classes : selectedClasses, step : 'add_classes' }, {

            onSuccess : (res) => {
                router.navigateTo('/assessments/termly/schedule/'+ assessmentId)
            },
            onError : (res) => {

                disabled = false;
                // console.log(res);
            },
        });
    }
    
    const selectAllClasses = () => {

        if(selectAll){

            selectAll = false;
            selectedClasses = []

        }else{

            selectAll = true;
            selectedClasses = classes
        }
    }

</script>

<div class="container my-12 space-x-10">
    <div class="shadow bg-white px-8 py-12 rounded-lg w-full">
        <div class="mx-auto max-w-4xl">
            <div class="flex items-center justify-between my-2 border-b border-gray-50 pb-8">
                <div>
                    <div class="text-xl font-medium min-w-max text-gray-700">Assessment Levels</div>
                    <div class="text-gray-400 text-sm">Select the levels to be assessed below</div>
                </div>
                
                <div class="ml-72 pt-4 max-w-3xl">
                    <Button { disabled } on:click={ assignClasses } buttonText={ assessmentId ? "Update & Continue" : "Save & Continue"} className="px-6 min-w-max" />
                </div>
            </div>
    
            <div class="my-12 space-y-3 w-full">
                <div class="flex w-full text-sm max-w-4xl">
                    <span class="flex-shrink-0 w-[18rem] text-gray-700">Select levels to be assessed</span>
                    <div class="w-full space-y-3">
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
        </div> 
    </div>
</div>