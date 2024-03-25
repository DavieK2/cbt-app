<script>
    import { onMount } from "svelte";
    import { page } from "@inertiajs/svelte";
    import { router } from "../../../util";
    import ExamIntro from "../Student/ExamIntro.svelte";
    import Exam from "../Student/Exam.svelte";


    let hasExamSession = false;
    let assessmentId = $page.props.assessmentId

    let submitModal = false;

    let assessmentTitle;
    let assessmentInstructions;
    let assessmentDuration;
    let assessmentTotalQuestions;
    let assessmentTotalMarks;
    let timeLeft;
    let studentName;
    let studentCode;
    let studentPhoto

    onMount( () => {
        router.getWithToken('/api/cbt/session/student/' + assessmentId, {
            onSuccess: (res) => {
                studentCode = res.data.studentCode;
                hasExamSession = res.data.hasStarted;
                assessmentTitle = res.data.assessmentTitle;
                assessmentInstructions = res.data.instructions;
                assessmentTotalQuestions = res.data.totalQuestions;
                assessmentDuration = res.data.assessmentDuration
                assessmentTotalMarks = res.data.totalScore
                timeLeft = res.data.remainingTime
                studentName = res.data.studentName;
                studentPhoto = res.data.studentPhoto;
                studentName = res.data.studentName;
            }
        })
    });

    const startAssessment = () => {

        router.postWithToken('/api/cbt/start-session/student/'+assessmentId, {}, {
            onSuccess : (res) => {
                hasExamSession = true
            }
        }); 
    }


</script>

<svelte:document on:contextmenu|preventDefault />

{ #if hasExamSession }
    <Exam { studentCode } { studentPhoto } { studentName } { assessmentTitle } { assessmentId } { timeLeft } />
{ :else }
    <ExamIntro { assessmentTitle } { assessmentDuration } { assessmentInstructions } { assessmentTotalMarks } { assessmentTotalQuestions } on:start-assessment={ startAssessment }/>
{/if}



