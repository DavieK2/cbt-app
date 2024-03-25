<script>
    import { router } from "../../../util";
    import { page } from "@inertiajs/svelte";
    import Button from "../../components/button.svelte";
    import Input from "../../components/input.svelte";
    import { onMount } from "svelte";

    let assessmentId = $page.props.assessmentId;

    let studentCode;
    let subjects = []

    let studentData = {

        studentCode : "",
        studentName : "",
        studentLevel : "",
        studentPhoto : "",
    }

    let selectedSubjects = [];

    $: disabled = ! ( studentData.studentCode && studentData.studentLevel && studentData.studentName )

    onMount(() => {
        
        router.getWithToken('/api/assessment-subjects/' + assessmentId, {
            onSuccess : (res) => {
                subjects = res.data
            }
        })
    });

    const getStudentData = () => {

        router.postWithToken('/api/student/check-in/get', { studentId : studentCode.value }, {
            onSuccess : (res) => {
                studentData.studentCode = res.data.studentCode
                studentData.studentName = res.data.studentName
                studentData.studentLevel = res.data.studentClass
                studentData.studentPhoto = res.data.studentPhoto
            }
        })
    }

    const checkinStudent = () => {
        router.postWithToken('/api/student/check-in/' + assessmentId, { studentId : studentCode.value, subjects: selectedSubjects }, {
            onSuccess : (res) => {

                studentData = {}
                selectedSubjects = []
            }
        })
    }

</script>


<div class="flex min-h-screen w-screen">
    <div class="container min-h-screen flex flex-1 flex-col justify-center px-20">
        <div class="flex flex-col py-12">
            <h1 class="font-bold text-2xl">Student Check In</h1>
            <div class="space-y-6 mt-12">
                <Input bind:this={ studentCode } label="Enter Student Code"/>
            </div>

           <div class="mt-10">
                <Button buttonText="Get Student Data" on:click={ getStudentData } />
           </div>
        </div>
    </div>
    <div class="container min-h-screen flex flex-col items-center pt-16 w-1/2 border-l border-2">
        <div class="flex flex-col py-12 w-full px-24">
            <h1 class="font-bold text-2xl">Student Info</h1>
            <div class="space-y-6 mt-20">
              <div class="h-40 w-60 bg-gray-200 rounded-lg">
                <img src={ studentData.studentPhoto } alt="" class="rounded-lg" >
              </div>
            </div>
            <div class="space-y-12 mt-12">
                <p class="font-semibold">Student Name: <span>{ studentData.studentName ?? "" }</span></p>
                <p class="font-semibold">Student Code: <span>{ studentData.studentCode  ?? ""}</span></p>
                <p class="font-semibold">Student Level: <span>{ studentData.studentLevel ?? "" }</span></p>
            </div>

            { #if studentData.studentCode }
                <div class="mt-6">
                    <p class="font-semibold my-6">Courses</p>
                    <ul class="space-y-4 text-gray-600 text-sm">
                        { #each subjects as subject }
                            <li class="flex space-x-2">
                                <input bind:group={ selectedSubjects } type="checkbox" value={ subject.subjectId }>
                                <span>{ subject.subjectName } ({ subject.subjectCode })</span>
                            </li>
                        { /each }
                    </ul>
                </div>
            {/if}

           <div class="mt-10">
                <Button { disabled } className="max-w-fit" buttonText="Check In Student" on:click={ checkinStudent } />
           </div>
        </div>
        
    </div>
</div>