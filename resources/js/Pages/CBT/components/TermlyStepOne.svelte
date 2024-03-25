<script>
    import { createEventDispatcher, onMount } from "svelte";
    import { router } from "../../../util";
    import Button from "../../components/button.svelte";
    import Input from "../../components/input.svelte";
    import Select from "../../components/select.svelte";

    let assessmentTypes = [];
    let academicSessions = [];
    let terms = [];

    let assessment;

    let disabled = false;

    let assessmentTypeId;
    let academicSessionId;
    let termId;
    let title
    let description;

    let assessmentId;

    let titleValue;
    let descriptionValue;
    let assessmentTypeIdValue = {};
    let academicSessionIdValue = {};
    let termIdValue = {};


    onMount(() => {

        let url = location.href;
        let param = url.split('?')[1];

        let query = new URLSearchParams(param);
        assessmentId = query.get('assessmentId');

        if( assessmentId ){

            router.get('/api/assessment/' + assessmentId, {

                onSuccess : (res) => {
                    
                   assessment = res.data.assessment;
                   
                   titleValue = assessment.title;
                   descriptionValue = assessment.description
                   termIdValue.value = assessment.termId
                   termIdValue.placeholder = assessment.term
                   assessmentTypeIdValue.value = assessment.assessmentTypeId
                   assessmentTypeIdValue.placeholder = assessment.assessmentType
                   academicSessionIdValue.value = assessment.academicSessionId
                   academicSessionIdValue.placeholder = assessment.academicSession

                }
            })
        }
        
        router.get('/api/assessment-types', {
            onSuccess : (res) => {
                assessmentTypes = res.data.flatMap((assessmentType) => [{ placeholder : assessmentType.type,  value : assessmentType.id }]);
            }
        })

        router.get('/api/sessions', {
            onSuccess : (res) => {
                academicSessions = res.data.flatMap((session) => [{ placeholder : session.session,  value : session.id }]);
            }
        })

        router.get('/api/terms', {
            onSuccess : (res) => {
                terms = res.data.flatMap((term) => [{ placeholder : term.term,  value : term.id }]);
            }
        })
    })


    const createAssessment = () => {
        
        disabled = true;

        let data = { 
            assessmentTypeId : assessmentTypeId.value, 
            title : title.value, 
            description : description.value, 
            step : "create_assessment", 
            isStandalone : false, 
            schoolTermId : termId.value,
            academicSessionId : academicSessionId.value
        };
        
        router.post('/api/assessment/create', data , {
            onSuccess : (res) => {
                router.navigateTo('/assessments/termly/classes/'+res.data.assessmentId);
            },
            onError : (res) => {

                disabled = false;
                // console.log(res)
            }
        })
    }

    const updateAssessment = () => {
        
        disabled = true;

        let data = { 
            assessmentId,
            assessmentTypeId : assessmentTypeId.value, 
            title : title.value, 
            description : description.value, 
            step : "create_assessment", 
            isStandalone : assessment.isStandalone, 
            schoolTermId : termId.value,
            academicSessionId : academicSessionId.value
        };
        
        router.post('/api/assessment/update', data , {
            onSuccess : (res) => {
                router.navigateTo('/assessments/termly/classes/'+res.data.assessmentId);
            },
            onError : (res) => {
                // console.log(res)
                disabled = false;
            }
        })
    }


</script>

<div class="container my-12 space-x-10">
    <div class="shadow bg-white px-8 py-12 rounded-lg w-full">
        <div class="mx-auto max-w-4xl">
            <div class="flex items-center justify-between my-2 border-b border-gray-50 pb-8">
                <div class="space-y-2">
                    <div class="text-xl font-medium min-w-max text-gray-700">
                        Assessment Details
                    </div>
                    <div class="text-gray-500 text-sm">
                        Fill in the assessment details below
                    </div>
                </div>
    
                <div class="">
                    { #if assessmentId }
                        <Button { disabled } on:click={ updateAssessment } buttonText="Update & Continue" className="px-8" />
                    {:else}
                        <Button { disabled } on:click={ createAssessment } buttonText="Save & Continue" className="px-8" />
                    {/if}
                </div>
            </div>
    
            <div class="my-12 space-y-6 w-full">
                <div class="flex w-full items-center text-sm max-w-4xl">
                    <span class="flex-shrink-0 w-[18rem] text-gray-800 font-semibold">Enter Assessment Title</span>
                    <div class="w-full">
                        <Input value={ titleValue } bind:this={ title } />
                    </div>
                </div>
                <div class="flex w-full items-center text-sm max-w-4xl">
                    <div class="flex-shrink-0 w-[18rem] text-gray-800 font-semibold">
                        Select Assessment Type
                    </div>
                    <div class="w-full">
                        <Select bind:this={ assessmentTypeId  }
                            value={ assessmentTypeIdValue.value }
                            placeholder={ assessmentTypeIdValue.placeholder ? assessmentTypeIdValue.placeholder :  "Select an Assessment Type"}
                            className="text-sm py-2.5"
                            isSelected={ assessmentTypeIdValue.value ? true : false }
                            options={ assessmentTypes }
                        />
                    </div>
                </div>

                <div class="flex w-full items-center text-sm max-w-4xl">
                    <div class="flex-shrink-0 w-[18rem] text-gray-800 font-semibold">
                        Select Academic Session
                    </div>
                    <div class="w-full">
                        <Select bind:this={ academicSessionId  }
                            value={ academicSessionIdValue.value }
                            placeholder={academicSessionIdValue.placeholder ? academicSessionIdValue.placeholder : "Select Academic Session"}
                            className="text-sm py-2.5"
                            isSelected={ academicSessionIdValue.value ? true : false }
                            options={ academicSessions }
                        />
                    </div>
                </div>

                <div class="flex w-full items-center text-sm max-w-4xl">
                    <div class="flex-shrink-0 w-[18rem] text-gray-800 font-semibold">
                        Select Semester
                    </div>
                    <div class="w-full">
                        <Select bind:this={ termId  }
                            value={ termIdValue.value }
                            placeholder={ termIdValue.placeholder ? termIdValue.placeholder : "Select Semester"}
                            className="text-sm py-2.5"
                            isSelected={ termIdValue.value ? true : false }
                            options={ terms }
                        />
                    </div>
                </div>
                
                <div class="flex w-full items-center text-sm max-w-4xl pt-4">
                    <label class="w-[18rem] flex-shrink-0 text-gray-800 font-semibold" for="">Enter Assessment Description</label>
                    <textarea bind:this={ description } class="border-0 w-full focus:outline-none ring-1 ring-gray-300 focus:ring-gray-500 rounded-lg resize-none p-4 border-gray-300 text-gray-800 text-sm text-left" cols="30" rows="5">{ descriptionValue ?? '' }</textarea>
                </div>
            </div>
        </div>
        
    </div>
</div>