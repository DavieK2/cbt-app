<script>
    import { onMount } from "svelte";
    import { page } from "@inertiajs/svelte";
    import { router } from "../../../../util";
    import ExamIntro from "../../Student/ExamIntro.svelte";
    import Exam from "../../Student/Exam.svelte";


    let hasExamSession = false;
    let assessmentId = $page.props.assessmentId
    let assessmentCode = $page.props.assessmentCode

    let submitModal = false;

    let assessmentTitle;
    let assessmentInstructions;
    let assessmentDuration;
    let assessmentTotalQuestions;
    let assessmentTotalMarks;
    let timeLeft;
    let studentName;
    let studentCode;
    let studentPhoto;
    let subjectId

    onMount( () => {
       
        subjectId =  sessionStorage.getItem('subject'); 

        router.getWithToken(`/api/cbt/t/session/student/${assessmentId}/${subjectId}`, {

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
            }
        })
    });

    const startAssessment = () => {

        router.postWithToken(`/api/cbt/t/start-session/student/${ assessmentId }/${ subjectId }`,{}, {
            onSuccess : (res) => {
                hasExamSession = true
            }
        }); 
    }

</script>

<svelte:document on:contextmenu|preventDefault />

{ #if hasExamSession }
    <Exam { assessmentCode } { studentName } { assessmentTitle } { studentCode } { studentPhoto } { assessmentId } { timeLeft } { subjectId } />
{ :else }
    <ExamIntro { assessmentTitle }  { assessmentDuration } { assessmentInstructions } { assessmentTotalMarks } { assessmentTotalQuestions } on:start-assessment={ startAssessment }/>
{/if}
