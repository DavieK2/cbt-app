<script>
    import Select from "../../../components/select.svelte";
    import Icons from "../../../components/icons.svelte";
    import Layout from "../../../layouts/Layout.svelte";
    import { onMount } from "svelte";
    import { page } from "@inertiajs/svelte";
    import { router } from "../../../../util";
    import Dropdown from "../../../components/dropdown.svelte";


    let assessmentId = $page.props.assessmentId;
    let assessmentTitle = $page.props.assessmentTitle;
    let selectedClassId;
    let classID;
    let assessmentClasses = [];
    let subjects = [];

    onMount(() => {

        router.getWithToken('/api/assessment-classes/' + assessmentId, {
            onSuccess : (res) => {
                assessmentClasses = res.data.flatMap((grade) => [{ placeholder : grade.class_name, value: grade.id, classCode: grade.class_code }] );
            }
        });
        
    });

    const getSubjects = (classId) => {

        classID = classId;
        
        selectedClassId = assessmentClasses.filter((grade) => grade.value === classId)[0]?.classCode

        router.getWithToken('/api/assessment-subjects/' + assessmentId + "?classId=" + classId, {
            onSuccess : (res) => {
                subjects = res.data
            }
        });
    }

    const viewAssessmentQuestions = (subjectId) => {
        router.navigateTo(`/questions/create/t/${assessmentId}/${subjectId}/${selectedClassId}`);  
    }

    const publishAssesssment = (subject) => {

        let shouldPublish = subject.published ? false : true;


        router.postWithToken('/api/assessment/publish/termly', { subjectId: subject.subjectId, classId: selectedClassId, assessmentId, shouldPublish }, {
            onSuccess: ( res ) => {
                
                getSubjects( classID )
            }
        })
    }
    
</script>

<Layout>
   <div class="container">
        <div class="my-28">
            <div class="flex items-center justify-between">
                <div class="flex space-x-3 items-center">
                    <Icons icon="chart" className="h-6 w-6" />
                    <span class="mx-2 text-lg font-medium text-gray-800">{ assessmentTitle }</span>
                </div>
            </div>
            <div class="flex flex-row justify-start min-h-[24rem] w-full bg-white rounded-lg mt-10 border border-gray-100">
                <div class="flex flex-col items-center min-h-full shrink-0 w-80 border-r border-gray-100 px-5 py-8">
                    <div class="w-full">
                        <p class="font-semibold min-w-max pb-5 border-b border-gray-100">Levels</p>
                        <div class="mt-10 space-y-3 text-sm">
                            { #each assessmentClasses as grade, index }
                                <div class="flex space-x-2 items-center w-full">
                                    <button  on:click={ () => getSubjects(grade.value) } class={` ${ selectedClassId == grade.classCode ? "border-green-700" : "border-gray-300" } flex items-center shrink-0 justify-center h-[2.7rem] w-[2.7rem] border-2 rounded-lg`}>
                                        <Icons icon="check" className={`${ selectedClassId == grade.classCode ? "stroke-green-700" : "stroke-gray-300" }`} />
                                    </button>
                                    <div class="w-full">
                                        <button type="button" class={` ${ selectedClassId == grade.classCode ? "border-green-700" : "border-gray-300" } flex p-2.5 border-2  w-full rounded-lg items-center justify-between space-x-2`}>
                                            <span class={ `${ selectedClassId == grade.classCode ? "text-green-700" : "text-gray-400" } font-normal` }>{ grade.placeholder }</span>
                                        </button>
                                    </div>
                                </div>
                            { /each }
                        </div>
                    </div>
                </div>
                <div class="flex flex-col px-5 py-8 w-full">
                    <p class="font-semibold min-w-max pb-5 border-b  border-gray-100">Courses</p>
                    <div class="space-y-6 mt-8">
                        { #each subjects as subject, index }
                             <div class={`flex items-center  space-x-10 ${ index != subjects.length - 1 ? 'border-b pb-6' : '' }`}>
                                 <div class="text-gray-800 text-sm flex flex-row min-w-[20rem]  items-center">{ subject.subjectName } ( { subject.subjectCode })</div>
                                 <div class={`px-4 py-2 text-center rounded ${ subject.published  ? 'bg-green-200 text-green-800' : 'bg-red-200 text-red-800' } w-28 text-sm`}>{ subject.published ? 'Published' : 'Unpublished' }</div>
                                 <Dropdown arrowColor="fill-gray-600" placeholder="Actions" className="bg-white border  border-gray-300 text-gray-600">
                                    <div class="flex flex-col space-y-2 items-start">
                                        <button type="button" on:click={ () => viewAssessmentQuestions(subject.subjectId) } class="text-sm p-2 rounded-lg hover:bg-gray-100 w-full text-left transition">View Questions</button>
                                        <button type="button" on:click={ () => publishAssesssment(subject) } class="text-sm p-2 rounded-lg hover:bg-gray-100 w-full text-left transition">{ subject.published ? 'Unpublish' : 'Publish' }</button>
                                    </div>
                                </Dropdown>
                             </div> 
                        { /each }
                     </div>
                </div>
            </div>
        </div>
   </div>
</Layout>