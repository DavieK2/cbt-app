<script>
    import { createEventDispatcher } from "svelte";
    import Button from "../../components/button.svelte";

    export let assessmentTitle;
    export let assessmentInstructions;
    export let assessmentDuration;
    export let assessmentTotalQuestions;
    export let assessmentTotalMarks;

    let agree;

    $: disabled = ! agree;

    const dispatch = createEventDispatcher();

</script>

<div class="w-full mx-auto flex flex-col md:px-0">
    <div class="bg-white z-10 shadow">
        <div class="max-w-3xl mx-auto">
            <div class="py-4 w-full">
                <h1 class="text-center text-lg font-semibold">
                    Assessment Instructions
                </h1>
            </div>
        </div>
    </div>
    <div class="flex-1 relative h-full z-0 overflow-hidden focus:outline-none ps">
        <main class="max-w-2xl mx-auto mt-10">
            <div class="w-full lg:flex lg:ltr:flex-row lg:rtl:flex-row-reverse flex-wrap px-2 py-2">
                <div class="py-4 w-full">
                    <h1 class="text-2xl font-semibold text-gray-800 leading-normal py-2">
                        { assessmentTitle }
                    </h1>
                    <div class="flex items-center mt-10">
                        <div>
                            <p class="font-mono text-sm leading-3 mb-2 text-green-600">
                                Total Duration
                            </p>
                            <p class="font-semibold leading-tight">
                                { assessmentDuration / 60 } Minutes
                            </p>
                        </div>
                        <div class="ml-11">
                            <p class="font-mono text-sm leading-3 mb-2 text-green-600">
                                No. of Questions
                            </p>
                            <p class="font-semibold leading-tight">
                                { assessmentTotalQuestions } Questions
                            </p>
                        </div>
                        <div class="ml-11">
                            <p class="font-mono text-sm leading-3 mb-2 text-green-600">
                                Total Marks
                            </p>
                            <p class="font-semibold leading-tight">
                                { assessmentTotalMarks } Marks
                            </p>
                        </div>
                    </div>

                   
                    <hr class="my-8 border-t border-gray-200" />
                    <h4 class="text-gray-900 font-semibold mb-4">
                        Exam Instructions
                    </h4>
                    <div class="prose text-gray-800 mb-2">
                        { assessmentInstructions }
                    </div>
                    <div class="prose text-gray-800 my-2 ">
                        Please do not refresh page or your assessment will be marked as submitted
                    </div>
                </div>
                <div class="py-4 w-full" >
                    <div>
                        <div class="w-full flex flex-col">
                            <div class="pt-2">
                                <input bind:checked={ agree } type="checkbox" id="agree_1"/>
                                <label for="agree_1" class="ltr:ml-2 rtl:mr-2 text-sm leading-normal font-normal text-gray-800 cursor-pointer">
                                    I've read all the instructions carefully and have understood them.
                                </label>
                            </div>
                            <div class="w-full block mt-10">
                               <Button { disabled } on:click={ () => dispatch('start-assessment') } buttonText="Start Assessment" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
