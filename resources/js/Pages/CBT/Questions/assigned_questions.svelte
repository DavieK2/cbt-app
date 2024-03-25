<script>
    import { createEventDispatcher, onMount } from "svelte";
    import QuestionCard from "../components/QuestionCard.svelte";
    import Select from "../../components/select.svelte";
    import Button from "../../components/button.svelte";
    import Icons from "../../components/icons.svelte";
    
    export let assignedQuestions = [];
    export let sections = [];
    export let disabled = false;
    
    let selectedQuestions = [];
    let initialQuestions = [];
    
    let selectedSection;
    let checkedAll = false;


    onMount(() => {

        initialQuestions = assignedQuestions;
        
    });

    const dispatch = createEventDispatcher();

    const selectQuestion = (questionId) => {

        selectedQuestions.push(questionId);
        selectedQuestions = selectedQuestions

    }

    const unselectQuestion = (questionId) => {

        selectedQuestions = selectedQuestions.filter((question) => question != questionId);
    }

    $: disabled = selectedQuestions.length === 0 || initialQuestions.length === 0 

    const checkAll = () => {

        if( checkedAll  ){

            selectedQuestions = selectedQuestions.filter((q) => ! initialQuestions.some( (s) => q === s.questionId ));


        }else{

            initialQuestions.flatMap((question) => [ question.questionId ]).forEach((sel) => {

                if( ! selectedQuestions.some( (ques) => ques === sel )){

                    selectedQuestions.push(sel);
                }
            })
            
            selectedQuestions = selectedQuestions;

        }

    }

    const massUnassign = () => {

        disabled = true ; 
        dispatch('mass-unassign', { selectedQuestions });

        selectedQuestions = [];
    }

    $: isChecked = (questionId) => selectedQuestions.some((question) => questionId === question );

    $: checkAllDisabled = initialQuestions.length === 0

    $: {
        if( ! selectedSection ){

            initialQuestions = assignedQuestions;

        }else{
            

            initialQuestions = assignedQuestions.filter((question) => {

               return question.sectionId === selectedSection
               
            } );
        }
       
    } 

    $: checkedAll = (initialQuestions.filter((q) => selectedQuestions.some( (s) => q.questionId === s)).length === initialQuestions.length) && ! (initialQuestions.length === 0)
</script>


<div class="fixed px-8 z-50 w-[calc(100vw-50rem)] min-w-[36rem] h-16 border-b border-r border-gray-100 bg-white shrink-0">
    <div class="flex space-x-8 items-center h-full justify-between">
        <div class="flex space-x-3 items-center w-full">
            <Select on:selected={ (e) => selectedSection = e.detail.value } on:deselected={ (e) => selectedSection = null } options={ sections } placeholder="All Sections" className="text-sm py-2.5"/>
        </div>
        
        <div class="flex space-x-2 text-sm">
            <button disabled={ checkAllDisabled } on:click={ checkAll } class="p-2.5 rounded-lg border-2 min-w-max max-w-min border-gray-600 text-gray-600 font-semibold disabled:border-gray-400 disabled:text-gray-400">{ checkedAll ? 'Uncheck All' : 'Check All'}</button>
            <Button on:click={ massUnassign } { disabled } buttonText="Unassign" className="text-sm" />
        </div>
    </div>
</div>

<div class="fixed w-[calc(100vw-50rem)] flex flex-col mt-16 h-[calc(100vh-17rem)] items-center bottom-0 pb-20 question overflow-y-auto bg-white">

    { #if initialQuestions.length == 0 }
        <div class="container h-[30rem]">
            <div class="flex items-center text-center rounded-lg h-full dark:border-gray-700">
                <div class="flex flex-col w-full max-w-sm px-4 mx-auto">
                    <div class="p-3 mx-auto text-blue-500 bg-blue-100 rounded-full dark:bg-gray-800">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                    </div>
                    <h1 class="mt-3 text-lg text-gray-800 dark:text-white">No Questions found</h1>
                    <p class="mt-2 text-gray-500 dark:text-gray-400">No question has been assigned to this assessment.</p>
                </div>
            </div>
        </div>
    { :else }

        <div class="w-full pl-4">
            { #each initialQuestions as question, index }
                <QuestionCard 
                    sectionTitle={ question.sectionTitle }
                    questionId={ question.questionId }
                    showOptions={ question.questionType != 'theory' ? true : false }
                    question={ question.question } 
                    questionOptions={ question.options }  
                    correctAnswer={ question.correctAnswer } 
                    questionNumber={ index + 1 }
                    questionScore={ question.questionScore } 
                    showCheckBox
                    checked={ isChecked(question.questionId) }
                    on:checked={ (e) => selectQuestion(e.detail.value) }
                    on:unchecked={ (e) => unselectQuestion(e.detail.value) }
                >
                    <div class="flex space-x-2 pt-5">
                        <button on:click={ () => dispatch('edit', question) } class="px-4 py-2.5 bg-gray-200 text-gray-700 rounded-lg text-xs">Edit</button>
                        <button on:click={ () => dispatch('unassign', question) } class="px-4 py-2.5 bg-red-500 text-red-50 rounded-lg text-xs">Remove</button>
                    </div>
                </QuestionCard>  
            { /each }
        </div>
        
    { /if }
</div>