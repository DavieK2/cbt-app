<script>
    import { router } from "../../../util";
    import { page } from "@inertiajs/svelte";

    import Button from "../../components/button.svelte";


    let assessmentId = $page.props.assessmentId;
    let assessmentCode = $page.props.assessmentCode;

    const logout = () => {

        router.post(`/cbt/${assessmentId}/logout`, {}, {
                onSuccess : (res) => {
                    window.location.replace('/cbt/' + assessmentCode );
                }
        });
    }
</script>



<svelte:document on:contextmenu|preventDefault />

<div class="min-h-screen w-screen flex flex-col items-center justify-center container px-20">
   <h1 class="font-semibold text-3xl">You have already completed this Exam</h1>
    <div class="mt-20">
        <Button buttonText="Sign Out" className="max-w-fit" on:click={ logout } />
    </div>
  
</div>