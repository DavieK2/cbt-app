<script>
    import { onMount } from "svelte";
    import Icons from "../../../components/icons.svelte";
    import Layout from "../../../layouts/Layout.svelte";
    import Assessment from "../../components/Assessment.svelte";
    import TermlyStepTwo from "../../components/TermlyStepTwo.svelte";
    import { page } from '@inertiajs/svelte';
    
    let assessmentId = $page.props.assessmentId;

    let step = {
        step : 2,
        stepOneUrl: '/assessments/termly?assessmentId=' + assessmentId,
        placeholder : "Level Details"
    }

    onMount(() => {

        if( assessmentId && $page.props.edit ){
            step.stepThreeUrl = '/assessments/termly/schedule/' + assessmentId;
         }
    });

</script>

<Layout>
    <div class="mt-20 flex items-center justify-between border-b bg-white">
        <div class="flex space-x-3 items-center py-4 px-8">
            <Icons icon="chart" className="h-5 w-5" />
            <span class="mx-2 text-sm font-medium text-gray-700">Assessments</span>
            <Icons icon="chevron_right" fill="#242121EB" className="h-4 w-4" />
            <span class="text-sm font-medium text-gray-700">Create</span>
        </div>
    </div>
   <div class="w-full mx-auto h-full">
        <Assessment { step } >
            <TermlyStepTwo />
        </Assessment>
   </div>
</Layout>