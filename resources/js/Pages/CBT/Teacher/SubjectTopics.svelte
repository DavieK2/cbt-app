<script>
    import { onMount } from "svelte";
    import DataTable from "../../components/data_table.svelte";
    import Dropdown from "../../components/dropdown.svelte";
    import Layout from "./components/layout.svelte";
    import { router } from "../../../util";
    import { inertia } from "@inertiajs/svelte"
    import Button from "../../components/button.svelte";
    import Select from "../../components/select.svelte";
    import SlidePanel from "../../components/slide_panel.svelte";
    import Input from "../../components/input.svelte";
    import Icons from "../../components/icons.svelte";


    let headings = ['S|N', 'Subject', 'Topic', 'Class', 'Action'];
    let topics = []

    let disabled = false;

    let classes = [];
    let subjects = [];

    let createTopicClasses = [];
    let selectedClasses = []

    let selectedClass;
    let selectedSubject;


    let createTopicSelectedSubject;

    let slideFormState;

    let topicData = {
        topicTitle: "",
        topicId: "",
        topicSubject: ""
    }

    let initTopicData = JSON.stringify(topicData);
    
    const panelState = {
        create : "Add New Topic",
        edit : "Edit Topic",
    }
    
    let slidePanelTitle;

    let showSlidePanel = false

    const closeSheet = () => {

        topicData = JSON.parse(initTopicData);
        createTopicClasses = [];
        selectedClasses = [];
        showSlidePanel = false
    }

    const openSlidePanel = (state) => {

        slidePanelTitle = state;
        slideFormState = state;

        showSlidePanel = true
    }


    onMount(() => {
        router.getWithToken('/api/get-subjects/', {
            onSuccess : (res) => {
                subjects = res.data.flatMap((subject) => [{ placeholder : subject.subjectName, value: subject.subjectId } ]);
            }
        });
    })

    const getClasses = (subjectId) => {

        return router.getWithToken('/api/get-class-subjects/' + subjectId, {
            onSuccess : (res) => {
                return res.data;
            }
        })
    }

    const populateClasses = async (subjectId) => {

        selectedSubject = subjectId;

        classes = await getClasses(subjectId);
        classes = classes.flatMap((grade) => [{ placeholder : grade.class_name, value: grade.class_code } ]);
        
    }

    const populateTopicClasses =  async (subjectId) => {
        createTopicSelectedSubject = subjectId
        createTopicClasses = await getClasses(subjectId);
    }


    $: isSelected = (gradeId) => {
        return selectedClasses.some((selected) => selected.class_code == gradeId);
    }

    const selectClass = (grade) => {

        if(isSelected(grade.class_code)){
            selectedClasses = selectedClasses.filter((selected) => selected.class_code != grade.class_code);
            return ;
        }

        selectedClasses.push(grade);
        selectedClasses = selectedClasses;
    }

    const addNewTopic = () => {

        disabled = true;

        router.postWithToken('/api/topic/create', { title: topicData.topicTitle, subjectId: createTopicSelectedSubject, classes: selectedClasses.flatMap((grade) => [grade.class_code] ) }, {
            onSuccess : (res) => {
                closeSheet();
                disabled = false;
            }
        })
    }

    const getTopics = () => {

        router.postWithToken('/api/topics/get/', { subjectId: selectedSubject, classId: selectedClass }, {
            onSuccess: (res) => {
                topics = res.data
            }
        })
    }

    const editTopic = (topic) => {


        topicData.topicId = topic.topicId;
        topicData.topicTitle = topic.topic;
        topicData.topicSubject = selectedSubject;

        router.getWithToken(`/api/topics/classes/${topicData.topicId}/${topicData.topicSubject}`, {
            onSuccess : (res) => {
                selectedClasses = res.data
            }
        })

        openSlidePanel(panelState.edit);
    }

    const updateTopic = () => {
        
        disabled = true;

        router.postWithToken('/api/topic/update', { topicId: topicData.topicId,  title: topicData.topicTitle, subjectId: createTopicSelectedSubject, classes: selectedClasses.flatMap((grade) => [{classCode: grade.class_code, classId : grade.classId}] ) }, {
            onSuccess : (res) => {
                getTopics();
                closeSheet();
                disabled = false;
            }
        })
    }

</script>

<Layout>
    <div class="container">
        <div class="my-28">
           
            <div class="flex justify-between">
                <h3 class="text-lg font-semibold">Subject Topics</h3>
                <Button on:click={ () => openSlidePanel(panelState.create) } buttonText="New Topic" className="max-w-min min-w-max bg-green-600 focus:outline-none hover:bg-green-700 focus:bg-green-600 focus:ring-green-200" />
            </div>
         
            <div class="flex flex-col border border-gray-300 rounded-lg w-full h-full bg-white mt-8 pt-4 pb-8 px-4">
                <span class="text-base text-gray-700 font-semibold">Filters</span>
                <div class="flex items-center mt-3 space-x-3 w-full">
                    <Select on:selected={ (e) => populateClasses(e.detail.value) } on:deselected={ () => { selectedSubject = null ; classes = []} } options={ subjects }  optionsStyling="text-sm text-gray-800" placeholder="Select Subject" className="w-full text-sm text-gray-400 py-2.5" />
                    <Select on:selected={ (e) => selectedClass =  e.detail.value } on:deselected={ () => selectedClass = null } options={ classes }  optionsStyling="text-sm text-gray-800" placeholder="Select Class" className="w-full text-sm text-gray-400 py-2.5" />
                    <Button on:click={ getTopics }  buttonText="Search" className="w-40 text-sm py-3" />
                    <Button on:click  buttonText="Reset" className="w-40 text-sm py-3" />
                </div>
            </div>
            
            <DataTable { headings } >
                { #each topics as topic, index }
                    <tr>
                        <td class="px-4 py-4 text-sm text-gray-800 dark:text-gray-300 whitespace-nowrap">{ index + 1  }</td>
                        <td class="px-4 py-4 text-sm text-gray-800 dark:text-gray-300 whitespace-nowrap">{ topic.subject }</td>
                        <td class="px-4 py-4 text-sm text-gray-800 dark:text-gray-300 whitespace-nowrap">{ topic.topic }</td>
                        <td class="px-4 py-4 text-sm text-gray-800 dark:text-gray-300 whitespace-nowrap">{ topic.class }</td>
                        <td class="px-4 py-4 text-sm text-gray-800 dark:text-gray-300 whitespace-nowrap">
                            <div class="w-40">
                                <Dropdown arrowColor="fill-gray-600" placeholder="Actions" className="bg-white border  border-gray-300 text-gray-600">
                                    <button on:click={ () => editTopic(topic) } class="hover:bg-gray-100 p-3 text-sm rounded transition text-left">Edit Topic</button>
                                    <button class="hover:bg-gray-100 p-3 text-sm rounded transition text-left">Delete Topic</button>
                                </Dropdown>
                            </div>
                        </td>
                    </tr>
                { /each }
            </DataTable>
        </div>
    </div>
    
</Layout>


<SlidePanel title={ slidePanelTitle } showSheet={ showSlidePanel } on:close-button={ closeSheet }>
    <div class="flex flex-col space-y-6 p-3">
        <div>
            <Input on:input={ (e) => topicData.topicTitle = e.detail.input } value={ topicData.topicTitle } label="Enter Topic" />
        </div>
        
        <div>
            <Select on:selected={ (e) => populateTopicClasses(e.detail.value) } on:deselected={ () => createTopicClasses = [] } options={ subjects } value={ topicData.topicSubject }  optionsStyling="text-sm text-gray-800" placeholder="Select Subject" className="w-full text-sm text-gray-700 py-2.5" label="Select Subject" />
        </div>

        <p class="text-sm text-gray-800">Select Classes</p>
        <div class="w-full space-y-2.5">
            { #each createTopicClasses as grade }
                <div class="flex space-x-2 items-center w-full">
                    <button  on:click={ () => selectClass(grade) } class={`flex items-center shrink-0 justify-center h-[3.2rem] w-[3.2rem] border-2 ${ isSelected(grade.class_code) ? 'border-green-700' : 'border-gray-300' } rounded-lg`}>
                        <Icons icon="check" className={`${ isSelected(grade.class_code) ? "stroke-green-700" : "stroke-gray-300" }`} />
                    </button>
                    <div class="w-full">
                        <button type="button" class={`flex p-3 border-2 ${ isSelected(grade.class_code) ? 'border-green-700' : 'border-gray-300' }  w-full rounded-lg items-center justify-between space-x-2`}>
                            <span class={`${ isSelected(grade.class_code) ? "text-green-700" : "text-gray-400" }`} >{ grade.class_name }</span>
                        </button>
                    </div>
                </div>
            {/each}
        </div>

        <div class="w-20 pt-6">
            { #if slideFormState == panelState.edit }
                <Button { disabled } on:click={ updateTopic } buttonText="Update Topic" className="min-w-max max-w-min"/>
            { :else }
                <Button { disabled } on:click={ addNewTopic } buttonText="Add Topic" className="min-w-max max-w-min"/>
            {/if}
        </div>
    </div>
</SlidePanel>