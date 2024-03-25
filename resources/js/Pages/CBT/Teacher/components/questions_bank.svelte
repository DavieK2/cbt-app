<script>
    import { createEventDispatcher, onMount } from "svelte";
    import QuestionCard from "../../components/QuestionCard.svelte";
    import Select from "../../../components/select.svelte";

    export let questions = [];
    export let sections = [];

    const dispatch = createEventDispatcher();

    const loadMore = (node) => {

       setTimeout(() => {

            const observer = new IntersectionObserver((entries) => {

                if(entries[0].isIntersecting){
                   dispatch('load-more-questions');
                }
            
            }, 
            {
                rootMargin: "100px"
            });

            observer.observe(node);

       }, 500);
    }
     
</script>


<div class="fixed flex items-center justify-center px-8 z-50 w-[calc(100vw-50rem)] h-32 border-b bg-white">
   <div class="w-full space-y-3">
        <div class="grid grid-cols-2 gap-3">
            <Select options={ sections } placeholder="Class" className="text-sm"/>
            <Select options={ sections } placeholder="Topic" className="text-sm"/>
        </div>
        <div class="grid grid-cols-3 gap-3">
                <Select options={ sections } placeholder="Term" className="text-sm"/>
                <Select options={ sections } placeholder="Session" className="text-sm"/>
                <Select options={ sections } placeholder="Assessment Type" className="text-sm"/>
        </div>
   </div>
</div>
<div class="fixed pb-52 question mt-16 overflow-y-auto h-[calc(100vh-21rem)] bottom-0 w-[calc(100vw-50rem)] bg-white">
    { #each questions as question, index }
        <QuestionCard
            sectionTitle={ question.sectionTitle }
            questionId={ question.questionId }
            showOptions={ question.questionType != 'theory' ? true : false }
            question={ question.question } 
            questionOptions={ question.options }  
            correctAnswer={ question.correctAnswer } 
            questionNumber={ index + 1 }
            questionScore={ question.questionScore } 
        >
            <div class="flex space-x-2 pt-5">
                <button on:click={ () => dispatch('edit', question) } class="px-4 py-2.5 bg-gray-200 text-gray-700 rounded-lg text-xs">Edit</button>
                <button class="px-4 py-2.5 bg-red-500 text-red-50 rounded-lg text-xs">Delete</button>
        </div>
        </QuestionCard> 
    {/each}
    <!-- <div use:loadMore class="h-5 w-full"></div> -->
</div>

<style>
    .question:empty{
        display: none;
    }
</style>