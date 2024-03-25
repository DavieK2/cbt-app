<script>
    import { createEventDispatcher, onMount } from "svelte";
    import QuestionCard from "../../components/QuestionCard.svelte";
    import Select from "../../../components/select.svelte";

    export let questions = [];
    export let sections = [];

    let selectedSection;

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
    
    $: filterQuestions = () => {

        return questions.map((question) => {

            if( ! selectedSection ){

                question.isVisible = true;

                return question;
            }

            if( question.sectionId != selectedSection ){

                question.isVisible = false;

            }else{

                question.isVisible = true;
            }

            return question;
            
        } )
    }

    
</script>


<div class="fixed px-8 z-50 w-[calc(100vw-50rem)] min-w-[36rem] h-16 border-b bg-white shrink-0">
    <div class="flex space-x-5 items-center h-full">
        <p class="font-bold text-sm text-gray-800">Sections:</p>
        <Select on:selected={ (e) => selectedSection = e.detail.value } on:deselected={ (e) => selectedSection = null } options={ sections } placeholder="All Sections" className="text-sm"/>
        <p class="text-sm font-bold min-w-max">Total Questions: { questions.length}</p>
    </div>
</div>
<div class="fixed pb-16 shrink-0 question mt-16 overflow-y-auto h-[calc(100vh-17rem)] bottom-0 w-[calc(100vw-50rem)] bg-white">
    { #each filterQuestions() as question, index }
        { #if question.isVisible }
             <QuestionCard
                sectionTitle={ question.sectionTitle }
                questionId={ question.questionId }
                question={ question.question } 
                questionOptions={ question.options }  
                correctAnswer={ question.correctAnswer } 
                questionNumber={ index + 1 }
                questionScore={ question.questionScore } 
                showOptions={ question.questionType != 'theory' ? true : false }
             >
                <div class="flex space-x-2 pt-5">
                    <button on:click={ () => dispatch('edit', question) } class="px-4 py-2.5 bg-gray-200 text-gray-700 rounded-lg text-xs">Edit</button>
                    <button class="px-4 py-2.5 bg-red-500 text-red-50 rounded-lg text-xs">Delete</button>
                </div>
            </QuestionCard> 
        {/if}
    {/each}
    <!-- <div use:loadMore class="h-5 w-full"></div> -->
</div>

<style>
    .question:empty{
        display: none;
    }
</style>