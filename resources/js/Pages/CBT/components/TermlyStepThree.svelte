<script>
    import { onMount } from "svelte";
    import Datetimepicker from "../../components/datetimepicker.svelte";
    import Icons from "../../components/icons.svelte";
    import Input from "../../components/input.svelte";
    import Select from "../../components/select.svelte";
    import { router } from "../../../util";
    import { page } from "@inertiajs/svelte";
    import Button from "../../components/button.svelte";


    let assessmentClasses = [];
    let classSubjects = [];
    let selectedSubjects = [];
    let assessmentId = $page.props.assessmentId;
    let selectedClassId;
    let selectedClassName;
    let selectAll = false;

    let disabled = false

    onMount(() => {

        router.getWithToken('/api/assessment-subjects/' + assessmentId, {
            onSuccess : (res) => {
               selectedSubjects = res.data
            }
        });

        // if( sessionStorage.getItem('selectedSubjects') && sessionStorage.getItem('assessmentId') == assessmentId ){
            
        //     let data = sessionStorage.getItem('selectedSubjects')
        //     selectedSubjects = JSON.parse(data);
            
        //     selectedClassId = sessionStorage.getItem('classId');
        //     selectedClassName = sessionStorage.getItem('className');

        //     getClassSubjects(selectedClassId, selectedClassName);
        // }

        // sessionStorage.setItem('assessmentId', assessmentId);

        router.getWithToken('/api/assessment-classes/' + assessmentId, {
            onSuccess : (res) => {
                assessmentClasses = res.data.flatMap((grade) => [{ placeholder : grade.class_name, value: grade.id }] );
            }
        });
    })

    const getClassSubjects = (id, className) => {
        
        selectedClassId = id;
        selectedClassName = className;

        sessionStorage.setItem('classId', selectedClassId);
        sessionStorage.setItem('className', selectedClassName);

        router.getWithToken('/api/class/subject/' + id, {

            onSuccess: (response) => {

                classSubjects = response.data;
                
                let filteredSubjects = selectedSubjects.filter( (subject) => ( subject.classId  == selectedClassId  ) );

                classSubjects.forEach((subject) => {
                    filteredSubjects = filteredSubjects.filter((sub) => sub.subjectId != subject.subjectId)
                })
              
                classSubjects = [ ...classSubjects, ...filteredSubjects ];

            },
            onError: (response) => {
                console.log(response)
            }
        })
    }

    $: isSelected = (subjectId) => {
        return selectedSubjects.some((selected) => (selected.subjectId == subjectId) && (selected.classId == selectedClassId));
    }

    const selectSubject = (subject) => {

        if( isSelected(subject.subjectId) ){
            selectedSubjects = selectedSubjects.filter((selected) => ( ! ( (selected.classId == selectedClassId )  && ( selected.subjectId == subject.subjectId ) ) ) );
            return ;
        }

        selectedSubjects.push(subject);
        selectedSubjects = selectedSubjects;
    }

    $: getSelectedClassSubjects = () => {

        return selectedSubjects.map((subject) => {
            
            if(subject.classId == selectedClassId){
                subject.isVisible = true
            } else {
                subject.isVisible = false
            }

            return subject;
        } );
    }

    const onDurationInput = (value, index) => {

        selectedSubjects[index].duration = value;
        sessionStorage.setItem('selectedSubjects', JSON.stringify(selectedSubjects));
    }

    const onStartDateInput = (value, index) => {

        selectedSubjects[index].startDate = value;
        sessionStorage.setItem('selectedSubjects', JSON.stringify(selectedSubjects));
    }

    const onEndDateInput = (value, index) => {

        selectedSubjects[index].endDate = value;
        sessionStorage.setItem('selectedSubjects', JSON.stringify(selectedSubjects));

    }

    const complete = () => {

        disabled = true;

        router.postWithToken('/api/assessment/termly/complete', { assessmentId, subjects: selectedSubjects, step : "complete_assessment" }, {
            onSuccess : (res) => {
                sessionStorage.removeItem('selectedSubjects');
                router.navigateTo('/assessments' );
            },
            onError : (res) => {
                disabled = false;
            }
        })
    }

    const selectAllSubjects = () => {

        if(selectAll){

            selectAll = false;
            selectedSubjects = []

        }else{

            selectAll = true;
            selectedSubjects = classSubjects
        }
    }



</script>

<div class="w-full px-8 pb-20">
    <div class="flex bg-white h-full w-full rounded-lg shadow">
        <div class="w-[24rem] shrink-0 h-full py-8 border-r">
            <div class="flex items-center w-full px-6 text-sm text-gray-600">
                <Select on:deselected={ () => selectedSubjects = [] } value={ selectedClassId } isSelected={ selectedClassId ? true : false } on:selected={ (e) => getClassSubjects(e.detail.value, e.detail.placeholder) } options={ assessmentClasses } placeholder={ selectedClassId ? selectedClassName : "Select a Level" } className="text-sm py-2.5"/>
            </div>
            <div class="p-6 w-full">
               <div class="w-full mt-4">
                    <h3 class="text-gray-800 font-bold pb-4">Courses</h3>
                    <div class="space-y-2">
                        { #if classSubjects.length == 0 }
                            <p class="text-gray-500 italic">No Course Available</p>
                        { :else }
                            <!-- <button  on:click={ selectAllSubjects } class={`flex items-center shrink-0 justify-center h-12 w-12 border-2 ${ selectAll ? 'border-green-700' : 'border-gray-300' } rounded-lg`}>
                                <Icons icon="check" className={`${ selectAll ? "stroke-green-700" : "stroke-gray-300" }`} />
                            </button> -->
                          { #each classSubjects as subject, index }
                                <div class="flex space-x-2 items-center w-full">
                                    <button  on:click={ () => selectSubject(subject) } class={`flex items-center shrink-0 justify-center h-12 w-12 border-2 ${ isSelected(subject.subjectId) ? 'border-green-700' : 'border-gray-300' } rounded-lg`}>
                                        <Icons icon="check" className={`${ isSelected(subject.subjectId) ? "stroke-green-700" : "stroke-gray-300" }`} />
                                    </button>
                                    <div class="w-full">
                                        <button type="button" class={`flex p-3 border-2 ${ isSelected(subject.subjectId) ? 'border-green-700' : 'border-gray-300' }  w-full rounded-lg items-center justify-between space-x-2`}>
                                            <span class={`text-sm ${ isSelected(subject.subjectId) ? "text-green-700" : "text-gray-400" }`} >{ subject.subjectName }</span>
                                        </button>
                                    </div>
                                </div>
                          { /each }
                        {/if}
                    </div>
               </div>
            </div>
        </div>
        <div class="relative flex flex-col flex-1 w-full h-full border-l py-8 overflow-auto">
            <div class="flex justify-end w-full px-8">
                <Button { disabled } on:click={ complete } buttonText="Complete" className="w-40" />
            </div>
            <div class="question z-50 subjects px-8 w-full  h-full space-y-6 mt-10">
                { #each getSelectedClassSubjects() as subject, index }
                    { #if subject.isVisible }
                        <div>
                            <h4 class="text-gray-800 text-sm pb-4 font-semibold">{ subject.subjectName }</h4>
                            <div class="flex items-center space-x-8">
                                <div class="w-24">
                                    <Input value={ ( (selectedSubjects[index].duration) ) ?? '' } on:input={ (e) => onDurationInput(e.detail.input, index) } type="number" pattern="[0-9]+" label="Duration" labelStyle="text-sm text-gray-500"  />
                                </div>
                                <div class="w-[16.25rem]">
                                    <Datetimepicker value={ selectedSubjects[index].startDate ?? '' } on:selected={ (e) => onStartDateInput(e.detail.datetime, index) } placeholder="Enter Start Date" showLabel={true} label="Start Date" labelStyling="text-sm font-normal text-gray-500 pt-[7px]"  />
                                </div>
                                <div class="w-[16.25rem]">
                                    <Datetimepicker value={ selectedSubjects[index].endDate ?? '' } on:selected={ (e) => onEndDateInput(e.detail.datetime, index) }  placeholder="Enter End Date" showLabel={true} label="End Date" labelStyling="text-sm font-normal text-gray-500 pt-[7px]"  />
                                </div>
                            </div>
                        </div>
                    {/if}
                {/each}
            </div>
            
        </div>
    </div>
</div>