<script>
    import { router } from "../../../util";
    import { createEventDispatcher, onMount } from "svelte";
    import { page } from "@inertiajs/svelte";
    import Icons from "../../components/icons.svelte";
    import Input from "../../components/input.svelte";
    import TextEditor from "./TextEditor.svelte";
    import Select from "../../components/select.svelte";


    export let edit = false;
    export let disabled = false;
    export let questionEdit = false;

    export let correctAnswer = ""; 
    export let options = [];
    export let initOptions;
    export let sections = [];
    export let topics = [];

    export let questionScore = "1";
    export let questionType = "";
    export let question = "";
    export let selectedTopic = "";
    export let selectedSection = "";
    export let questionId;
    export let questionBankId;
    export let questionImage = "";
    export let source;

    let image = [];


    let questionTypes = {
        objectives: 'objectives',
        picture: 'picture',
        video: 'video',
        math: 'math',
        theory: 'theory',
    }

    let selectedQuestionType = questionTypes.objectives;
   
    
    let assessmentId = $page.props.assessmentId


    const dispatch = createEventDispatcher();

    onMount(() => {
        initOptions = JSON.stringify(options)
    })

    const setCorrectAnswer = (answer) => correctAnswer = answer; 

    const addOption = () => {
        options.push({ content: "<p></p>", type: "" });
        options = options
    }

    const setOptionValue = (detail, index) => {

        options[index] = { content: detail.content, type: detail.type, alt: detail.alt };
    }

    const setQuestionValue = ( value ) => question = value;

    const removeOption = ( key ) => {
        options = options.filter((opt, index) => index != key );
    }

    const updateQuestion = () => {

        dispatch('saving');

        let data = {
            question: JSON.stringify(question),
            options,
            correctAnswer,
            questionScore,
            source,
            sectionId: selectedSection, 
            topicId: selectedTopic, 
            questionBankId,
            questionType: selectedQuestionType ?? questionType
        }

        router.postWithToken('/api/question/update/' + questionId, data, {
            onSuccess : (response) => {
                dispatch('updated', { question : data });
            },
            onError : (response) => {

                dispatch('error');
            }
        })
    }

    const saveQuestion = () => {

        dispatch('saving');

        let data = {
            question: JSON.stringify(question),
            options,
            correctAnswer,
            questionScore,
        }
        
        router.postWithToken('/api/question/create/' + assessmentId, { ...data, sectionId: selectedSection, topicId: selectedTopic, questionBankId, questionType: selectedQuestionType }, {
            onSuccess : (response) => {

                data.questionId = response.data.questionId;

                dispatch('saved', { question : { ...data, sectionId: selectedSection, topicId: selectedTopic } });
            },
            onError : (response) => {

                dispatch('error');
            }
        })
    }

    const onQuestionInput = (e) => question = e.target.value;

    const setSelectedQuestionType = (questionType) => selectedQuestionType = questionType; 

    const removeQuestionImage = () => {
        image = [];
        questionImage = ""
    }

    $: isCorrectAnswer = (option) => option?.length > 0 && option === correctAnswer;

    $: {

        if( image[0] ){

            let reader = new FileReader();

            reader.readAsDataURL(image[0]);

            reader.onload = () => {
                questionImage = reader.result
            }
        }
    }

    $: {
        if( selectedSection ){
            selectedQuestionType = sections.filter((section) => section.value === selectedSection)[0]?.questionType;
        }
        if( selectedQuestionType === questionTypes.theory ){
            questionScore = "0";
            options = options
        }else{
            questionScore = "1";
            options = options
        }
        
    }
    
</script>

<div class="flex flex-col px-3 pb-4">
    <div class="pb-6">

        <div class="space-y-4 pb-4 my-4">
            <p class="text-gray-800 font-semibold text-sm">Select Question Section</p>
            { #key selectedSection }
                <Select options={ sections } on:selected={ (e) => selectedSection = e.detail.value } on:deselected={ (e) => selectedSection = null } value={ selectedSection } placeholder="Select Question Section" className="text-sm ring-2"/>
            {/key}
        </div>

        <div class="space-y-4 pb-4 my-4">
            <p class="text-gray-800 font-semibold text-sm">Select Question Topic</p>
            { #key selectedTopic }
                <Select options={ topics } on:selected={ (e) => selectedTopic = e.detail.value } on:deselected={ (e) => selectedTopic = null } value={ selectedTopic } placeholder="Select Question Topic" className="text-sm ring-2" />
            { /key }
        </div>

        <div class="space-y-4 my-4">
            <p class="text-gray-800 font-semibold text-sm">Question</p>
            { #key ( question && questionEdit) }
                <TextEditor on:input={ (e) => setQuestionValue(e.detail) } id="questionEditor" content={ question } showTools />
            { /key }
        </div>

        { #if selectedQuestionType != questionTypes.theory }

            <p class="text-gray-800 font-semibold text-sm pt-4 pb-2">Options</p>
            <ul class="flex flex-col w-full space-y-2 text-xs text-gray-600 pt-2">
                { #each options as option, index }
                    <div class="flex space-x-2 items-center w-full">
                        <button on:click={ () => setCorrectAnswer(option?.content ?? option) } class={`flex items-center shrink-0 justify-center h-11 w-11 border-2 ${ isCorrectAnswer(option?.content ?? option) ? 'border-green-500' : 'border-gray-300' } rounded-lg`}>
                            <Icons icon="check" className={ isCorrectAnswer(option?.content ?? option)  ? 'stroke-green-500' : 'stroke-gray-400'} />
                        </button>
                    
                        <div class="w-full">
                            { #key ( questionEdit ) }
                                <TextEditor on:input={ (e) => setOptionValue(e.detail, index) } id={ index } content={ option.htmlContent } isOptionEditor />
                            { /key }
                        </div>
                    
                        { #if (index + 1) == options.length }
                            <button on:click={ addOption } class={`flex items-center shrink-0 justify-center h-11 w-11 border-2 ${ 'border-gray-300' } rounded-lg`}>
                                <Icons icon="plus" className={'stroke-gray-400'} />
                            </button>
                        {/if}

                        { #if index > 1 }
                            <button on:click={ () => removeOption(index) } class={`flex items-center shrink-0 justify-center h-11 w-11 border-2 ${'border-gray-300' } rounded-lg`}>
                                <Icons icon="trash" className={'stroke-gray-400'} />
                            </button>
                        {/if}
                    </div>
                {/each}
            </ul>

        {/if}

        <div class="w-32 mt-6 space-y-2">
            <p class="text-gray-800 font-semibold text-sm ">Question Score</p>
            <Input on:input={ (e) => questionScore = e.detail.input } value={ questionScore ?? "1" } type="number" className="ring-2"  />
        </div>
        <div class="flex space-x-2 pt-5">
            <button on:click={ () => dispatch('cancel') } class="px-4 py-2.5 bg-gray-200 text-gray-700 rounded-lg text-sm">Cancel</button>
            { #if edit }
                <button { disabled } on:click={ updateQuestion } class="px-4 py-2.5 bg-gray-800 text-gray-50 rounded-lg text-sm disabled:bg-gray-500">Update Question</button>
            { :else }   
                <button { disabled } on:click={ saveQuestion } class="px-4 py-2.5 bg-gray-800 text-gray-50 rounded-lg text-sm  disabled:bg-gray-500">Save Question</button>
            {/if}
        </div>
    </div>
</div>