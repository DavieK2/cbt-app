<script>
    import { router } from "../../../util";
    import { page } from "@inertiajs/svelte";
    import Button from "../../components/button.svelte";
    import Input from "../../components/input.svelte";
    import { onMount } from "svelte";

    let assessmentId = $page.props.assessmentId;
    let role = $page.props.role

    let studentCode;
    let studentId;
    let hasCheckedIn = false;
    let subjects = []

    let studentData = {

        studentCode : "",
        studentName : "",
        studentLevel : "",
        studentPhoto : "",
    }

    let selectedSubjects = [];

    $: disabled = ! ( studentData.studentCode && studentData.studentLevel && studentData.studentName && !(hasCheckedIn))

    $: {

        if( role != 'admin' && role != 'checkin' ){
            
            router.navigateTo(`/adminer/login`)
        }
        
    }

    const getSubjects = () => {
        
        router.getWithToken(`/api/student-assessment-subjects/${assessmentId}/${studentId}`, {
            onSuccess : (res) => {
                subjects = res.data
            }
        })
    };

    const getStudentData = () => {

        router.postWithToken('/api/student/check-in/get/' + assessmentId, { studentId : studentCode.value }, {
            onSuccess : (res) => {

                studentData.studentCode = res.data.studentCode
                studentData.studentName = res.data.studentName
                studentData.studentLevel = res.data.studentClass
                studentData.studentPhoto = res.data.studentPhoto

                studentId = res.data.studentId
                hasCheckedIn = res.data.hasCheckedIn

                getSubjects();
            }
        })
    }

    const checkinStudent = () => {

        disabled = true

        router.postWithToken('/api/student/check-in/' + assessmentId, { studentId : studentCode.value, subjects: selectedSubjects }, {
            
            onSuccess : (res) => {

                studentData = {}
                selectedSubjects = []
            }
        })
    }

</script>


<div class="flex min-h-screen w-screen">
    <div class="container min-h-screen flex w-[30rem]  flex-col justify-center px-20">
        <div class="flex flex-col py-12">
            <h1 class="font-extrabold text-4xl mb-16">CBT PORTAL</h1>
            <h1 class="font-bold text-2xl">Student Check In</h1>
            <div class="space-y-6 mt-12">
                <Input bind:this={ studentCode } label="Enter Student Code"/>
            </div>

           <div class="mt-10">
                <Button buttonText="Get Student Data" on:click={ getStudentData } />
           </div>
        </div>
    </div>
    <div class="container min-h-screen flex flex-col flex-1 items-center pt-16">
        <div class="flex flex-col py-12 w-full px-16 bg-gray-50 rounded-xl">
            { #if hasCheckedIn }
                <p class="p-4 rounded-lg mb-12 bg-green-100 text-green-800 font-medium text-sm border border-green-200 min-w-max max-w-min">Student has been checked in</p>
            {/if}
            <h1 class="font-bold text-2xl">Student Information</h1>
            <div class="space-y-6 mt-12">
              <div class="h-48 w-48 bg-gray-200 rounded-xl bg-cover" style={`background-image: url(${studentData.studentPhoto})`}></div>
            </div>
            <div class="space-y-6 mt-12">
                <p class="font-medium text-gray-900">Student Name: &nbsp;&nbsp;&nbsp;<span class="font-normal text-gray-600">{ studentData.studentName ?? "" }</span></p>
                <p class="font-medium text-gray-900">Student Code: &nbsp;&nbsp;&nbsp;<span class="font-normal text-gray-600">{ studentData.studentCode  ?? ""}</span></p>
                <p class="font-medium text-gray-900">Student Level:&nbsp;&nbsp;&nbsp;<span class="font-normal text-gray-600">{ studentData.studentLevel ?? "" }</span></p>
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