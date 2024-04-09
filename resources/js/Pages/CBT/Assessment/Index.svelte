<script>
    import Button from "../../components/button.svelte";
    import Dropdown from "../../components/dropdown.svelte";
    import Icons from "../../components/icons.svelte";
    import Select from "../../components/select.svelte";
    import DataTable from "../../components/data_table.svelte";
    import Layout from "../../layouts/Layout.svelte";
    import { onMount } from "svelte";
    import { router } from "../../../util";
    import { auth } from "../../../auth";
    import { inertia, page } from "@inertiajs/svelte";
    import SlidePanel from "../../components/slide_panel.svelte";


    let role = $page.props.role;

    let assessments = [];
    let assessmentTypes = [];
    let assessmentCategories = [
        { placeholder : "Quiz", value : 1},
        { placeholder : "Exam", value : 0}

    ];
    let terms = [];
    let sessions = [];

    let assessmentCategoriesFilter;
    let assessmentTypesFilter;
    let termsFilter;
    let sessionFilter;

    let assessment = {}

    let showSlidePanel = false;

    let headings = ['SN', 'Title', 'Assessment Category', 'Assessment Type', 'Semester', 'Session', 'Status', 'Actions']


    onMount(() => {

        router.get('/api/assessments?perPage=15', {
            onSuccess: (response) => {
                assessments = response.data
            }
        });

        router.get('/api/assessment-types', {
            onSuccess: (response) => {
                assessmentTypes = response.data
                assessmentTypes = assessmentTypes.flatMap((assessmentType) => [{ placeholder : assessmentType.type, value: assessmentType.id } ]);
            }
        });

        router.get('/api/terms', {
            onSuccess: (response) => {
                terms = response.data
                terms = terms.flatMap((term) => [{ placeholder : term.term, value: term.id } ]);
            }
        });

        router.get('/api/sessions', {
            onSuccess: (response) => {
                sessions = response.data
                sessions = sessions.flatMap((session) => [{ placeholder : session.session, value: session.id } ]);
            }
        });
    })

    const resetFilter = () => {
     
    }

    const showSheet = () => showSlidePanel = true;

    const closeSheet = () => {

        assessment = {}
        showSlidePanel = false
    }

    const publish = (assessmentId, status) => {

        router.post('/api/assessment/publish', { assessmentId, publish: status } , {
            onSuccess: (response) => {

                assessments = assessments.map((assessment) => {

                    if(assessment.assessmentId == assessmentId) {
                        assessment.status = status ? 'Published' : 'Unpublished';
                    }
                    return assessment;
                })
            }
        });
    }

    const viewAssessment = (data) => {
        assessment = data
        showSheet();
    }

    const viewAssessmentQuestions = (assessment) => {

        if( assessment.isStandalone === "Quiz"){
            router.navigateTo('/assessments/quiz/question-banks/'+ assessment.assessmentId);
            return;
        }

        router.navigateTo('/assessments/termly/view/' + assessment.assessmentId)
    }

    const manageAssessments = (assessment, isStandalone) => {

        if( isStandalone == 'Quiz' ){

            return router.navigateTo('/questions/create/s/' + assessment.assessmentId);
        }

        return router.navigateTo('/assessments/termly/manage/' + assessment.assessmentId)
    }

    const editAssessment = (assessmentId, isStandalone) => {

        if(isStandalone == 'Quiz'){
            router.navigateTo('/assessments/standalone?assessmentId=' + assessmentId);
            return;
        }

        router.navigateTo('/assessments/termly?assessmentId=' + assessmentId);
    }

    const viewResults = (assessmentId, isStandalone) => {

        if(isStandalone == 'Quiz'){
            router.navigateTo('/assessments/results/s/' + assessmentId);
            return;
        }

        router.navigateTo('/assessments/results/t/' + assessmentId);
    }

    const editSchedule = (assessmentId) => {

        router.navigateTo('/assessments/termly/schedule/' + assessmentId);
    }

</script>

<Layout>
   <div class="container">
        <div class="my-28">
            <div class="flex items-center justify-between">
                <div class="flex space-x-3 items-center">
                    <Icons icon="chart" className="h-6 w-6" />
                    <span class="mx-2 text-lg font-medium text-gray-800">Assessments</span>
                </div>
                { #if auth.can(role, 'edit', 'assessments')}
                    <Dropdown className="text-sm text-left" placeholder="New Assessment" >
                        <div class="flex flex-col w-full">
                            <a use:inertia href="/assessments/standalone" class="hover:bg-gray-100 p-3 text-sm rounded transition w-full">Quiz</a>
                            <a use:inertia href="/assessments/termly"  class="hover:bg-gray-100 p-3 text-sm rounded transition w-full">Exam</a>
                        </div>
                    </Dropdown>
                {/if}
                
            </div>
            <div class="flex flex-col border border-gray-100 rounded-lg w-full h-full bg-white mt-8 pt-4 pb-8 px-4">
                <span class="text-base text-gray-700">Filters</span>
                <div class="flex items-center mt-3 space-x-3 w-full">
                    <Select bind:this={ assessmentCategoriesFilter } options={ assessmentCategories } optionsStyling="text-sm text-gray-800" placeholder="Select Asst. Category" className="w-full text-sm text-gray-400" />
                    <Select bind:this={ assessmentTypesFilter } options={ assessmentTypes } optionsStyling="text-sm text-gray-800" placeholder="Select Asst. Type" className="w-full text-sm text-gray-400" />
                    <Select bind:this={ termsFilter } options={ terms } optionsStyling="text-sm text-gray-800" placeholder="Select Semester" className="w-full text-sm text-gray-400" />
                    <Select bind:this={ sessionFilter } options={ sessions } optionsStyling="text-sm text-gray-800" placeholder="Select Session" className="w-full text-sm text-gray-400" />
                    <Button on:click={ resetFilter }  buttonText="Reset" className="w-40 text-sm py-3" />
                </div>
            </div>
            
            <DataTable { headings }>
                { #each assessments as assessment, index(assessment.assessmentId) }
                    <tr>
                        <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300 whitespace-nowrap">{ index + 1  }</td>
                        <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300 whitespace-nowrap">{ assessment.title }</td>
                        <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300 whitespace-nowrap">{ assessment.isStandalone }</td>
                        <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300 whitespace-nowrap">{ assessment.assessmentType }</td>
                        <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300 whitespace-nowrap">{ assessment.term }</td>
                        <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300 whitespace-nowrap">{ assessment.session }</td>
                        <td class="px-4 py-4 text-xs text-gray-600 dark:text-gray-300 whitespace-nowrap ">
                            <div class={`px-4 py-2 text-center rounded ${ assessment.status == 'Published' ? 'bg-green-200 text-green-800' : 'bg-red-200 text-red-800' }`}>
                                { assessment.status }
                            </div>
                        </td>
                        <td class="px-4 py-4 text-sm text-gray-800 dark:text-gray-300 whitespace-nowrap">
                            <div class="flex space-x-3">
                                <Dropdown arrowColor="fill-gray-600" placeholder="Actions" className="bg-white border  border-gray-300 text-gray-600">
                                    <div class="flex flex-col">
                                        { #if auth.can(role, 'view', 'assessments')}
                                            <button on:click={ () => viewAssessment(assessment) } class="hover:bg-gray-100 p-3 text-sm rounded transition text-left">View Assessment</button>
                                        {/if}
                                        
                                        { #if auth.can(role, 'edit', 'assessments')}
                                            <button on:click={ () => editAssessment(assessment.assessmentId, assessment.isStandalone) } class="hover:bg-gray-100 p-3 text-sm rounded transition text-left">Edit Assessment</button>
                                        {/if}

                                        { #if auth.can(role, 'edit', 'assessments')}
                                            { #if assessment.isStandalone === 'Exam' }
                                                <button on:click={ () => editSchedule(assessment.assessmentId) } class="hover:bg-gray-100 p-3 text-sm rounded transition text-left">Edit Schedule</button>
                                            {/if}
                                        {/if}
                                       
                                        { #if auth.can(role, 'view', 'assessments')}
                                            <button on:click={ () => viewAssessmentQuestions(assessment) } class="hover:bg-gray-100 p-3 text-sm rounded transition text-left">View Question Banks</button>
                                        {/if}

                                        { #if auth.can(role, 'view', 'assessments')}
                                            { #if assessment.isStandalone === 'Exam' }
                                                <button on:click={ () => manageAssessments(assessment, assessment.isStandalone) } class="hover:bg-gray-100 p-3 text-sm rounded transition text-left">Manage Assessments</button>
                                            { /if }
                                        {/if}

                                        { #if auth.can(role, 'view', 'assessments')}
                                            { #if assessment.isStandalone === 'Quiz' }
                                                <button on:click={ () => manageAssessments(assessment, assessment.isStandalone) } class="hover:bg-gray-100 p-3 text-sm rounded transition text-left">View Questions</button>
                                            { /if }
                                        {/if}

                                        { #if auth.can(role, 'edit', 'assessments')}
                                            { #if assessment.status == 'Published'}
                                                <button on:click={ () => publish(assessment.assessmentId, false) } class="hover:bg-gray-100 p-3 text-sm rounded transition text-left">Unpublish</button>
                                            { :else }
                                                <button on:click={ () => publish(assessment.assessmentId, true) } class="hover:bg-gray-100 p-3 text-sm rounded transition text-left">Publish</button>
                                            {/if}
                                        {/if}
                                       
                                        { #if auth.can(role, 'edit', 'assessments')}
                                            <button on:click={ () => viewResults(assessment.assessmentId, assessment.isStandalone) } class="hover:bg-gray-100 p-3 text-sm rounded transition text-left">View Results</button>
                                        {/if}

                                        <a href={ `/students/check-in/${assessment.assessment_code}`} target="_blank" class="hover:bg-gray-100 p-3 text-sm rounded transition text-left">Check In Students</a>
                                    </div>
                                </Dropdown>
                            </div>
                        </td>
                    </tr>
                {/each}
            </DataTable>
        </div>
   </div>
</Layout>

<SlidePanel title="Viewing Assessment" showSheet={ showSlidePanel } on:close-button={ closeSheet }>


    <div class="relative px-3 flex flex-col justify-between items-start mt-5">
        <p class="text-xs uppercase text-gray-500 mt-6 pb-2 font-semibold">Assessment Title</p>
        <p class="pb-6 w-full text-sm text-gray-800">
            { assessment.title ?? 'Not Available' }
        </p>
        <p class="text-xs uppercase text-gray-500 mt-6 pb-2 font-semibold">Instructions</p>
        <p class="pb-6 w-full text-sm text-gray-800">
            { assessment.description ?? 'Not Available' }
        </p>
        <div class="my-6 border border-gray-300 rounded-lg w-full py-4">
            <div class="flex ltr:flex-row rtl:flex-row-reverse items-center">
                <div class="space-y-2 border-r px-6">
                    <p class="font-mono text-sm leading-3 mt-2 text-gray-800">
                       Total Duration
                    </p>
                    <p class="text-lg font-semibold leading-tight text-green-600">{ assessment.duration ? assessment.duration + ' Minutes' : 'Not Available' }</p>  
                </div>
                <div class="space-y-2 border-r px-6">
                    <p class="font-mono text-sm leading-3 mt-2 text-gray-800">
                        No. of Questions
                     </p>
                     <p class="text-lg font-semibold leading-tight text-green-600">{ assessment.totalQuestions ?  assessment.totalQuestions + ' Questions' : 'Not Available' }</p>  
                </div>
                <div class="space-y-2 px-6">
                    <p class="font-mono text-sm leading-3 mt-2 text-gray-800">
                        Total Marks
                     </p>
                     <p class="text-lg font-semibold leading-tight text-green-600">{ assessment.totalMarks ?  assessment.totalMarks + ' Marks' : 'Not Available' }</p>  
                </div>
            </div>
        </div>
        <div class="w-full flex flex-col sm:gap-4 items-center mt-2">
            <div class="w-full mt-4 p-6 rounded-lg bg-yellow-50 border border-yellow-100 flex flex-col gap-4">
                <p class="text-sm uppercase font-mono font-medium leading-3 text-yellow-600">
                   Assessment Dates
                </p>
                <div class="flex items-center text-gray-600">
                    <p class="text-sm font-mono leading-none text-gray-700 dark:text-gray-100 ltr:ml-2 rtl:mr-2">
                        <span class="font-semibold">Start Date:</span> { assessment.startDate ?? 'Not Available' }
                    </p>
                </div>
                <div class="flex items-center text-gray-600">
                    <p class="text-sm font-mono leading-none text-gray-700 dark:text-gray-100 ltr:ml-2 rtl:mr-2">
                        <span class="font-semibold">End Date:</span> { assessment.endDate ?? 'Not Available' }
                    </p>
                </div>
            </div>
        </div>

        <div class="w-full flex flex-col sm:gap-4 items-center mt-2">
            <div class="w-full mt-4 p-6 rounded-lg bg-blue-50 border border-blue-100 flex flex-col gap-4">
                <p class="text-sm uppercase font-mono font-medium leading-3 text-blue-900">
                   Assessment URL
                </p>
                <div class="flex items-center text-gray-600">
                    <p class="text-sm font-mono leading-none text-gray-700 dark:text-gray-100 ltr:ml-2 rtl:mr-2">
                      { assessment.assessmentLink ?? 'Not Available' }
                    </p>
                </div>
            </div>
        </div>
        
    </div>

</SlidePanel>