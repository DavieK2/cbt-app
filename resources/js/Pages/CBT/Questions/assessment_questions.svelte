<script>
    import { createEventDispatcher, onMount } from "svelte";
    import QuestionCard from "../components/QuestionCard.svelte";
    import Button from "../../components/button.svelte";
    import Select from "../../components/select.svelte";
    import Icons from "../../components/icons.svelte";
   
    export let disabled = false;

    let selectedQuestions = [];
    let initialQuestions = [];

    export let questions = [];
    export let sections = [];
    export let assessmentQuestionBanks = [];


    let selectedSection;
    let checkedAll = false;

    onMount(() => {

        initialQuestions = questions;
        
    });

    // export let questionCurrentPageNumber
    // export let questionLastPageNumber;

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

    const massAssign = () => {

        disabled = true ; 
        dispatch('mass-assign', { selectedQuestions });

        selectedQuestions = [];
    }

    $: isChecked = (questionId) => selectedQuestions.some((question) => questionId === question );

    $: checkAllDisabled = initialQuestions.length === 0

    $: {
        if( ! selectedSection ){

            initialQuestions = questions;

        }else{
            

            initialQuestions = questions.filter((question) => {

               return question.sectionId === selectedSection
               
            } );
        }
       
    } 

    $: checkedAll = (initialQuestions.filter((q) => selectedQuestions.some( (s) => q.questionId === s)).length === initialQuestions.length) && ! (initialQuestions.length === 0)

    
</script>

<!-- <svelte:body on:scroll={ () => console.log('scrolling') } /> -->

<div class="fixed px-8 z-50 w-[36rem] h-16 border-b border-r bg-white border-gray-100 shrink-0">
    <div class="flex space-x-8 items-center h-full justify-between">
        <div class="flex space-x-3 items-center w-full">
            <Select on:selected={ (e) => dispatch('get-question-bank', { value: e.detail.value }) } on:deselected={ (e) => { sections = []; questions = [] } } options={ assessmentQuestionBanks } placeholder="Select Question Bank" className="text-sm py-2.5 uppercase" optionsStyling="uppercase", placeholderStyling="uppercase"/>
        </div>
        
       
    </div>
</div>

<div class="fixed px-8 z-40 w-[36rem] h-16 border-b border-r border-gray-100 bg-white shrink-0 mt-16">
    <div class="flex space-x-8 items-center h-full justify-between">
        <div class="flex space-x-3 items-center w-full">
            <Select on:selected={ (e) => selectedSection = e.detail.value } on:deselected={ (e) => selectedSection = null } options={ sections } placeholder="All Sections" className="text-sm py-2.5"/>
        </div>
        
        <div class="flex space-x-2 text-sm">
            <button disabled={ checkAllDisabled } on:click={ checkAll } class="p-2.5 rounded-lg border-2 min-w-max max-w-min border-gray-600 text-gray-600 font-semibold disabled:border-gray-400 disabled:text-gray-400">{ checkedAll ? 'Uncheck All' : 'Check All'}</button>
            <Button on:click={ massAssign } { disabled } buttonText="Assign" className="text-sm" />
                <button { disabled } class="p-2.5 rounded-lg min-w-max max-w-min bg-red-600 hover:bg-red-700 font-semibold transition disabled:bg-red-400">
                    <Icons  icon="trash" className="stroke-white h-6 w-6" />
                </button>
        </div>
    </div>
</div>

<div class="fixed w-[36rem] flex flex-col mt-16 h-[calc(100vh-21rem)] items-center bottom-0 pb-20 border-r border-gray-100 question overflow-x-hidden overflow-y-auto pl-4 bg-white">
    { #each initialQuestions as question, index }
        <QuestionCard 
            hasBeenAssigned={ question.isAssigned }
            sectionTitle={ question.sectionTitle }
            questionId={ question.questionId }
            question={ question.question } 
            questionOptions={ question.options }  
            correctAnswer={ question.correctAnswer } 
            questionNumber={ index + 1 }
            questionScore={ question.questionScore } 
            showCheckBox={ question.isAssigned == 'Not Assigned' }
            checked={ isChecked(question.questionId) }
            showOptions={ question.questionType != 'theory' ? true : false }
            on:checked={ (e) => selectQuestion(e.detail.value) }
            on:unchecked={ (e) => unselectQuestion(e.detail.value) }
        >
            <div class="flex space-x-2 pt-5">
                <button on:click={ () => dispatch('edit', question) } class="px-4 py-2.5 bg-gray-200 text-gray-700 rounded-lg text-xs">Edit</button>
                <button class="px-4 py-2.5 bg-red-500 text-red-50 rounded-lg text-xs">Delete</button>
                <!-- <button on:click={ () => dispatch('assign', question) } class="px-4 py-2.5 bg-gray-800 text-gray-50 rounded-lg text-xs">Assign</button> -->
            </div>
        </QuestionCard> 
    { /each }
</div>

<style>
    .question:empty{
        display: none;
    }
</style>