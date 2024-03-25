<script>
    import { onMount } from "svelte";
    import { router } from "../../../../util";
    import { page } from "@inertiajs/svelte"
    import Button from "../../../components/button.svelte";

    let availableAssessments = []
    let assessmentId = $page.props.assessmentId
    let assessmentCode = $page.props.assessmentCode
    let assessmentTitle = $page.props.assessmentTitle

    onMount(() => {
        router.getWithToken('/api/cbt/t/session/student/'+assessmentId, {
            onSuccess: (res) => {
                availableAssessments = res.data
            }
        })
    })

    const startExam = (subjectCode) => {
        sessionStorage.setItem('subject', subjectCode);
        window.location.replace(`/cbt/${assessmentId}/t/i`);
    }

     const logout = () => {

        router.post(`/cbt/${assessmentCode}/logout`, {}, {
                onSuccess : (res) => {
                    window.location.replace('/cbt/' + assessmentCode );
                }
        });
    }

</script>


<svelte:document on:contextmenu|preventDefault />

<div class="flex flex-col items-center justify-center min-h-screen container">

    <div class=" text-5xl font-black mb-20 max-w-2xl text-center">{ assessmentTitle }</div>

    { #if availableAssessments.length === 0 }
        <div class="flex flex-col items-center justify-center w-full">
            <p class="text-2xl font-medium text-gray-600 text-center">No exams are available at the moment</p>
            <div class="mt-20">
                <Button buttonText="Sign Out" className="max-w-fit min-w-max" on:click={ logout } />
            </div>
        </div>

    { :else }
        <div class="grid grid-cols-3 gap-5 w-full">
            
            { #each availableAssessments as assessment }
                <div class="relative flex flex-col items-center justify-center p-6 w-96 h-80 bg-white border-2 border-gray-300 rounded-lg">
                    <div class="space-y-4">
                        <p class="font-bold text-lg text-gray-800">{ assessment.subjectName }</p>
                        <p class="text-gray-800">Duration: { assessment.duration / 60 } Mins</p>
                    </div>
                    <div class="absolute w-full bottom-0 inset-x-0 px-6 pb-6">
                        <Button on:click={ () => startExam(assessment.subjectCode ) } buttonText="Start" />
                    </div>
                </div>
            { /each }
        </div>
    { /if }
</div>

