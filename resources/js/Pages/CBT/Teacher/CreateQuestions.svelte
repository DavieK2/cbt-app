<script>
    import { onMount } from "svelte";
    import { router } from "../../../util";
    import { page } from "@inertiajs/svelte"
    import Button from "../../components/button.svelte";
    import Icons from "../../components/icons.svelte";
    import EditableQuestionCard from "../components/EditableQuestionCard.svelte";
    import Layout from "./components/layout.svelte";
    import QuestionsList from "./components/questions_list.svelte";
    import SlidePanel from "../../components/slide_panel.svelte";
    import CsvImportCard from "../components/CsvImportCard.svelte";
    import ImportMappingCard from "../components/ImportMappingCard.svelte";
    import QuestionBank from "./components/questions_bank.svelte";
    import Modal from "../../components/modal.svelte";

    let question = {
        question : "<p></p>",
        options : ["<p></p>","<p></p>"],
        correctAnswer : "",
        questionScore : "1",
        sectionId : "",
        topicId : "",
        source : ""
    };

    let questionType = QuestionsList;

    let importCard;

    let edit = false;
    let disabled = false;
    let questionEdit = false;
    let hasImportError = false;

    let importErrorsLink = '';

    let initQuestion = JSON.stringify(question);

    let classId = $page.props.classCode
    let assessmentId = $page.props.assessmentId
    let subjectId = $page.props.subjectId
    let questionBankId = $page.props.questionBankId

    let questions = [];
    let sections = [];
    let topics = [];
    let questionTypes = [];

    let selectedSection;
    let selectedQuestionType;

    let showSlidePanel = false;

    let slidePanelState;

    let slidePanelStates = {
        uploadQuestions : "Upload File",
        importQuestions : "Import Questions",
    }
    
    let headings = {}

    let mappings = [
        { placeholder : "Question", value: "question", isSelected: false },
        { placeholder : "Option", value: "options", isSelected: false },
        { placeholder : "Score", value: "questionScore", isSelected: false },
        { placeholder : "Correct Answer", value: "correctAnswer", isSelected: false },
    ];

    let importKey;

    let resetMappings = JSON.stringify(mappings);

    let mappedFields = {}

    $: getAvailableMappings = () => {

        return mappings?.filter( (field) => field.isSelected == false );
    }


    onMount(() => {
        
        getQuestions();
        getSections();
        getQuestionTopics();
        getQuestionTypes();

    })

    const getSections = () => {
        router.getWithToken(`/api/question-bank/sections/${questionBankId}`, {
            onSuccess : (res) => {
                sections = res.data.flatMap((section) => [{ placeholder: section.sectionTitle, value: section.sectionId, questionType: section.questionType }]);
            }
        })
    }
    
    const getQuestionTopics = () => {

        router.getWithToken(`/api/get-question-topics?questionBankId=${questionBankId}`, {
            onSuccess: (res) => {
                topics = res.data.flatMap((topic) => [{ placeholder: topic.topicTitle, value: topic.topicId }]);
            }
        })
    }

    const getQuestions = () => {

        router.getWithToken(`/api/get-questions/${questionBankId}`, {
            onSuccess: (res) => {
                questions = res.data
            }
        })
    }

    const getQuestionTypes = () => {

        router.getWithToken('/api/question-types', {
            onSuccess : (response) => {
                questionTypes = response.data.flatMap((questionType) => [{ placeholder : questionType, value: questionType } ]);
            },
            onError : (response) => {
                // console.log(response)
            }
        })
    }

    const createQuestion = (ques) => {
    
        disabled = false;

        questionEdit = ! questionEdit
        question = JSON.parse(initQuestion);

        question.sectionId = ques.sectionId;
        question.topicId = ques.topicId;

        getQuestions();
    }

    const editQuestion = (ques) => {

        let initEditQuestion = JSON.stringify(ques);

        edit = true

        questionEdit = ! questionEdit

        question = JSON.parse(initEditQuestion);

    }

    const updateQuestion = () => {

        disabled = false;
        edit = false;

        questionEdit = ! questionEdit

        question = JSON.parse(initQuestion);
        
        getQuestions();

    }

    const uploadQuestions = () => {

        if( disabled ) return;

        disabled = true;
        
        let data = new FormData();

        data.append('file', importCard.uploadFile);
        data.append('assessmentId', assessmentId);

        
        router.postFormWithToken('/api/question/import', data , {

            onSuccess : (res) => {

                importKey = res.data.key;
                headings = res.data.headings;
                slidePanelState = slidePanelStates.importQuestions

                setTimeout(() => disabled = false, 2000 );
            }
        })
    }

    const importQuestion = () => {
        
        if( disabled ) return;

        disabled = true;


        router.postWithToken('/api/question/import' , { mappings: mappedFields, assessmentId, key: importKey, classId, subjectId, questionBankId, questionType: selectedQuestionType, sectionId: selectedSection }, {
            onSuccess : (res) => {

                getQuestions();
                closeSheet();

                setTimeout(() => disabled = false, 2000 );

                if( res.data.errors ) {
                    
                    sessionStorage.setItem('importError', res.data.errors)

                    hasImportError = true;

                    importErrorsLink = res.data.errors;

                }
            },
            onError : (res) => {

                getQuestions();
                // closeSheet();
                
                setTimeout(() => disabled = false, 2000 );
                
            }
        })
    }

    const closeSheet = () => {
        showSlidePanel = false
        reset();
    }

    const showSheet = (state) => {

        reset();
        slidePanelState = state;
        showSlidePanel = true;

    }

    const reset = () => { 
        question = JSON.parse(initQuestion);
        mappings = JSON.parse(resetMappings);
        mappedFields = {}
    }


    const mapFields = (selected) => {

        let field = mappings.filter( (field) => (selected.value === field.value) && ( selected.value != "options" ) )[0];
        
        if( field ){
            field.isSelected = ! field.isSelected ;
        }

        if( (selected.selected) && ( selected.value != "options" ) ){
            mappedFields[selected.value] = Object.keys(headings)[selected.index]
        }
        
        if( (selected.selected) && ( selected.value == "options" ) ){
            mappedFields[selected.value] = mappedFields[selected.value] ? [ ...mappedFields[selected.value], Object.keys(headings)[selected.index] ] : [ Object.keys(headings)[selected.index] ];
        }

        if( (selected.deselected) && ( selected.value != "options" ) ){
            delete mappedFields[selected.value];
        }
        
        if( (selected.deselected) && ( selected.value == "options" ) ){
            mappedFields[selected.value] = mappedFields[selected.value]?.filter((mapped) => mapped != Object.keys(headings)[selected.index]);
        }

        mappings = mappings;
    }


</script>

<Layout >
    <div class="relative mt-20 overflow-x-auto">
         <div class="fixed ml-56 h-16 z-30 inset-x-0 flex items-center justify-between border-b bg-white">
             <div class="flex space-x-3 items-center py-4 px-8">
                 <Icons icon="chart" className="h-5 w-5" />
                 <span class="mx-2 text-sm font-medium text-gray-700">Assessments</span>
                 <Icons icon="chevron_right" className="h-4 w-4 fill-gray-700" />
                 <span class="text-sm font-medium text-gray-700">Questions</span>
             </div>
         </div>
 
        <div class="fixed ml-56 mt-16 z-30 inset-x-0 flex border-b items-center h-16 bg-white">
             <div class="px-8 w-[36rem] shrink-0 border-r h-full">
                   
             </div>
             <div class="px-8 py-4 w-[calc(100vw-50rem)] space-x-10 flex items-center justify-between">
                
                <div class="h-full w-full">
                    <div class="flex items-center h-full">
                        <button on:click={ () => questionType = QuestionsList } type="button" class={`text-xs py-2.5 px-8 ${ questionType != QuestionsList ? 'text-gray-400 bg-gray-100' : 'text-gray-600 border-2 border-gray-500' } rounded-s-lg transition min-w-max w-full font-semibold`}>Questions</button>
                        <button on:click={ () => questionType = QuestionBank } type="button" class={`text-xs py-2.5 px-8 ${ questionType != QuestionBank ? 'text-gray-400 bg-gray-100' : 'text-gray-600 border-2 border-gray-500' } rounded-e-lg transition min-w-max  w-full font-semibold`}>Question Bank</button> 
                    </div>      
                </div>
                <div class="h-16 w-[2px] bg-gray-200"></div>
                <div class="flex items-center space-x-2">
                    <Button on:click={ () => showSheet(slidePanelStates.uploadQuestions) } buttonText="Import Questions" className="min-w-max text-sm bg-green-600 hover:bg-green-500 focus:bg-green-600 focus:ring-green-300" />
                </div>
            </div>
         </div>
        
         <div class="fixed ml-56 mt-[13rem] w-[36rem] inset-x-0 bottom-0 bg-white border-r h-[calc(100vh-13rem)] overflow-y-auto">
            <div class="relative flex flex-col h-full">
                <div class="max-w-xl px-6 pt-8">
                    <EditableQuestionCard 
                        { questionBankId }
                        { topics } 
                        { sections } 
                        on:saving={ () => disabled = true } 
                        on:error={ () => disabled = false } 
                        on:updated={ updateQuestion } 
                        { disabled } 
                        { edit } 
                        { questionEdit } 
                        on:saved={ (e) => createQuestion(e.detail.question) } 
                        correctAnswer={ question.correctAnswer } 
                        question={ question.question } 
                        options={ question.options } 
                        questionScore={ question.questionScore } 
                        selectedSection={ question.sectionId } 
                        selectedTopic={ question.topicId } 
                        questionId={ question.questionId } 
                        source={ question.source }
                    />
                </div>
            </div>
         </div>
         <div class="ml-[50rem] fixed mt-52 min-w-[32rem] shrink-0 inset-0 bg-white min-h-screen overflow-auto">
            <div class="relative flex flex-col h-full">
                <div class="absolute inset-0 bg-white">
                    <div class="container h-[30rem]">
                        <div class="flex items-center text-center rounded-lg h-full dark:border-gray-700">
                            <div class="flex flex-col w-full max-w-sm px-4 mx-auto">
                                <div class="p-3 mx-auto text-blue-500 bg-blue-100 rounded-full dark:bg-gray-800">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                                    </svg>
                                </div>
                                <h1 class="mt-3 text-lg text-gray-800 dark:text-white">No questions found</h1>
                                <p class="mt-2 text-gray-500 dark:text-gray-400">There aren't any available questions.</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="absolute z-50 bg-white w-full">
                    <svelte:component on:edit={ (e) => editQuestion(e.detail) } this={ questionType } { sections } { questions }   />                        
                </div>
            </div>
         </div>
    </div>
 </Layout>

 <SlidePanel title={ slidePanelState } showSheet={ showSlidePanel } on:close-button={ closeSheet } >

    { #if (slidePanelState === slidePanelStates.uploadQuestions) }

       <CsvImportCard bind:this={ importCard } on:on-upload={ uploadQuestions } { disabled } />

    { /if }

    { #if (slidePanelState === slidePanelStates.importQuestions) }

        <ImportMappingCard 
            on:deselected={ (e) => mapFields(e.detail) }  
            on:selected={ (e) => mapFields(e.detail) } 
            on:import={ importQuestion } 
            on:back-button={ () => slidePanelState = slidePanelStates.uploadQuestions } 
            on:question-type={ (e) => selectedQuestionType = e.detail.value }
            on:de-question-type={ (e) => selectedQuestionType = null }
            on:section={ (e) => selectedSection = e.detail.value }
            on:de-section={ (e) => selectedSection = null }
            options={ getAvailableMappings() } 
            { headings } 
            { sections } 
            { questionTypes } 
            { disabled }
        />

    { /if }
</SlidePanel>


<Modal show={ hasImportError }>
    <div class="flex flex-col justify-center items-center h-full p-6 space-y-5">

        <div class="">
            <svg class="h-28 w-28" xmlns="http://www.w3.org/2000/svg" data-name="Layer 1" width="782.04441" height="701.88002" viewBox="0 0 782.04441 701.88002" xmlns:xlink="http://www.w3.org/1999/xlink"><path d="M609.48783,100.59015l-25.44631,6.56209L270.53735,187.9987,245.091,194.56079A48.17927,48.17927,0,0,0,210.508,253.17865L320.849,681.05606a48.17924,48.17924,0,0,0,58.61776,34.58317l.06572-.01695,364.26536-93.93675.06572-.01695a48.17923,48.17923,0,0,0,34.58309-58.6178l-110.341-427.87741A48.17928,48.17928,0,0,0,609.48783,100.59015Z" transform="translate(-208.9778 -99.05999)" fill="#f2f2f2"/><path d="M612.94784,114.00532l-30.13945,7.77236L278.68955,200.20385l-30.139,7.77223a34.30949,34.30949,0,0,0-24.6275,41.74308l110.341,427.87741a34.30946,34.30946,0,0,0,41.7431,24.62736l.06572-.01695,364.26536-93.93674.06619-.01707a34.30935,34.30935,0,0,0,24.627-41.7429l-110.341-427.87741A34.30938,34.30938,0,0,0,612.94784,114.00532Z" transform="translate(-208.9778 -99.05999)" fill="#fff"/><path d="M590.19,252.56327,405.917,300.08359a8.01411,8.01411,0,0,1-4.00241-15.52046l184.273-47.52033A8.01412,8.01412,0,0,1,590.19,252.56327Z" transform="translate(-208.9778 -99.05999)" fill="#f2f2f2"/><path d="M628.955,270.49906,412.671,326.27437a8.01411,8.01411,0,1,1-4.00241-15.52046l216.284-55.77531a8.01411,8.01411,0,0,1,4.00242,15.52046Z" transform="translate(-208.9778 -99.05999)" fill="#f2f2f2"/><path d="M620.45825,369.93676l-184.273,47.52032a8.01411,8.01411,0,1,1-4.00242-15.52046l184.273-47.52032a8.01411,8.01411,0,1,1,4.00241,15.52046Z" transform="translate(-208.9778 -99.05999)" fill="#f2f2f2"/><path d="M659.22329,387.87255l-216.284,55.77531a8.01411,8.01411,0,1,1-4.00242-15.52046l216.284-55.77531a8.01411,8.01411,0,0,1,4.00242,15.52046Z" transform="translate(-208.9778 -99.05999)" fill="#f2f2f2"/><path d="M650.72653,487.31025l-184.273,47.52033a8.01412,8.01412,0,0,1-4.00242-15.52047l184.273-47.52032a8.01411,8.01411,0,0,1,4.00242,15.52046Z" transform="translate(-208.9778 -99.05999)" fill="#f2f2f2"/><path d="M689.49156,505.246l-216.284,55.77532a8.01412,8.01412,0,1,1-4.00241-15.52047l216.284-55.77531a8.01411,8.01411,0,0,1,4.00242,15.52046Z" transform="translate(-208.9778 -99.05999)" fill="#f2f2f2"/><path d="M374.45884,348.80871l-65.21246,16.817a3.847,3.847,0,0,1-4.68062-2.76146L289.5963,304.81607a3.847,3.847,0,0,1,2.76145-4.68061l65.21247-16.817a3.847,3.847,0,0,1,4.68061,2.76145l14.96947,58.04817A3.847,3.847,0,0,1,374.45884,348.80871Z" transform="translate(-208.9778 -99.05999)" fill="#e6e6e6"/><path d="M404.72712,466.1822l-65.21247,16.817a3.847,3.847,0,0,1-4.68062-2.76146l-14.96946-58.04816A3.847,3.847,0,0,1,322.626,417.509l65.21246-16.817a3.847,3.847,0,0,1,4.68062,2.76145l14.96946,58.04817A3.847,3.847,0,0,1,404.72712,466.1822Z" transform="translate(-208.9778 -99.05999)" fill="#e6e6e6"/><path d="M434.99539,583.55569l-65.21246,16.817a3.847,3.847,0,0,1-4.68062-2.76145l-14.96946-58.04817a3.847,3.847,0,0,1,2.76145-4.68062l65.21247-16.817a3.847,3.847,0,0,1,4.68061,2.76146l14.96947,58.04816A3.847,3.847,0,0,1,434.99539,583.55569Z" transform="translate(-208.9778 -99.05999)" fill="#e6e6e6"/><path d="M863.63647,209.0517H487.31811a48.17928,48.17928,0,0,0-48.125,48.12512V699.05261a48.17924,48.17924,0,0,0,48.125,48.12507H863.63647a48.17924,48.17924,0,0,0,48.125-48.12507V257.17682A48.17928,48.17928,0,0,0,863.63647,209.0517Z" transform="translate(-208.9778 -99.05999)" fill="#e6e6e6"/><path d="M863.637,222.90589H487.31811a34.30948,34.30948,0,0,0-34.271,34.27093V699.05261a34.30947,34.30947,0,0,0,34.271,34.27088H863.637a34.30936,34.30936,0,0,0,34.27051-34.27088V257.17682A34.30937,34.30937,0,0,0,863.637,222.90589Z" transform="translate(-208.9778 -99.05999)" fill="#fff"/><circle cx="694.19401" cy="614.02963" r="87.85039" fill="#6c63ff"/><path d="M945.18722,701.63087H914.63056V671.07421a11.45875,11.45875,0,0,0-22.9175,0v30.55666H861.1564a11.45875,11.45875,0,0,0,0,22.9175h30.55666V755.105a11.45875,11.45875,0,1,0,22.9175,0V724.54837h30.55666a11.45875,11.45875,0,0,0,0-22.9175Z" transform="translate(-208.9778 -99.05999)" fill="#fff"/><path d="M807.00068,465.71551H616.699a8.01412,8.01412,0,1,1,0-16.02823H807.00068a8.01412,8.01412,0,0,1,0,16.02823Z" transform="translate(-208.9778 -99.05999)" fill="#e6e6e6"/><path d="M840.05889,492.76314H616.699a8.01412,8.01412,0,1,1,0-16.02823H840.05889a8.01411,8.01411,0,1,1,0,16.02823Z" transform="translate(-208.9778 -99.05999)" fill="#e6e6e6"/><path d="M807.00068,586.929H616.699a8.01412,8.01412,0,1,1,0-16.02823H807.00068a8.01411,8.01411,0,0,1,0,16.02823Z" transform="translate(-208.9778 -99.05999)" fill="#e6e6e6"/><path d="M840.05889,613.97661H616.699a8.01412,8.01412,0,1,1,0-16.02823H840.05889a8.01412,8.01412,0,1,1,0,16.02823Z" transform="translate(-208.9778 -99.05999)" fill="#e6e6e6"/><path d="M574.07028,505.04162H506.72434a3.847,3.847,0,0,1-3.84278-3.84278V441.25158a3.847,3.847,0,0,1,3.84278-3.84278h67.34594a3.847,3.847,0,0,1,3.84278,3.84278v59.94726A3.847,3.847,0,0,1,574.07028,505.04162Z" transform="translate(-208.9778 -99.05999)" fill="#e6e6e6"/><path d="M574.07028,626.25509H506.72434a3.847,3.847,0,0,1-3.84278-3.84278V562.46505a3.847,3.847,0,0,1,3.84278-3.84278h67.34594a3.847,3.847,0,0,1,3.84278,3.84278v59.94726A3.847,3.847,0,0,1,574.07028,626.25509Z" transform="translate(-208.9778 -99.05999)" fill="#e6e6e6"/><path d="M807.21185,330.781H666.91017a8.01411,8.01411,0,0,1,0-16.02823H807.21185a8.01411,8.01411,0,0,1,0,16.02823Z" transform="translate(-208.9778 -99.05999)" fill="#ccc"/><path d="M840.27007,357.82862H666.91017a8.01411,8.01411,0,1,1,0-16.02822h173.3599a8.01411,8.01411,0,0,1,0,16.02822Z" transform="translate(-208.9778 -99.05999)" fill="#ccc"/><path d="M635.85911,390.6071H506.51316a3.847,3.847,0,0,1-3.84277-3.84277V285.81706a3.847,3.847,0,0,1,3.84277-3.84277H635.85911a3.847,3.847,0,0,1,3.84277,3.84277V386.76433A3.847,3.847,0,0,1,635.85911,390.6071Z" transform="translate(-208.9778 -99.05999)" fill="#6c63ff"/></svg>
        </div>
       <div class="flex flex-col items-center space-y-6">
            <p class="font-bold text-gray-800 text-lg">There were some Import Errors</p>

            <a class="text-blue-500 text-sm" href={ importErrorsLink }>Click to Download</a>
       </div>

       <div class="pt-3">
            <Button on:click={ () => { sessionStorage.removeItem('importError'); hasImportError = false; importErrorsLink = '' } } buttonText="Close" className="text-sm" />
       </div>
       
    </div>
</Modal>