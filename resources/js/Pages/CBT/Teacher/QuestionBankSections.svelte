<script>
    import Button from "../../components/button.svelte";
    import Icons from "../../components/icons.svelte";
    import Layout from "./components/layout.svelte";

    import { page } from "@inertiajs/svelte";
    import { router } from "../../../util";
    import { onMount } from "svelte";
    import Table from "../../components/table.svelte";
    import SlidePanel from "../../components/slide_panel.svelte";
    import Input from "../../components/input.svelte";
    import Select from "../../components/select.svelte";


    let showAddSectionForm = false

    let sections = [];

    let questionTypes = [];

    let slideFormState;
    
    let disabled = false;

    const panelState = {
        createSection : "Add New Section",
        edit : "Edit Section",
    }
    
    let slidePanelTitle;

    let sectionData = {
        sectionTitle : "",
        sectionDescription : "",
        sectionId : "",
        questionType : ""
    }

    let initSectionData  = JSON.stringify(sectionData);


    let headings = ['S|N', 'Title', 'Description', 'Action']

    let questionBankId = $page.props.questionBankId;

    onMount(() => {

        getSections();
        getQuestionTypes();
        
    });

    const getQuestionTypes = () => {

        router.getWithToken('/api/question-types', {
            onSuccess : (res) => {

                questionTypes = res.data.flatMap( (questionType) => [{ placeholder: questionType.toUpperCase(), value: questionType }]);
            }
        })
    }

    const getSections = () => {

        router.getWithToken(`/api/question-bank/sections/${questionBankId}`, {
            onSuccess : (res) => {
                sections = res.data;
            }
        })
    }

    const addSectionsToQuestionBank = () => {
        
        router.navigateTo(`/teacher/questions/${questionBankId}`);

        // router.postWithToken('/api/question-bank/create/sections', { questionBankId }, {
        //     onSuccess : (res) => {
                
        //     }
        // });
    }

    const createSection = () => {
        
        disabled = true;

        router.postWithToken('/api/question-bank/create/section', { questionBankId, title: sectionData.sectionTitle, description: sectionData.sectionDescription, questionType: sectionData.questionType }, {
            onSuccess: (res) => {
                getSections();
                closeSheet();

                disabled = false;
            },
            onError: (res) => {
                disabled = false
            }
        });
    }

    const editSection = (section) => {

        sectionData = section;
        openSectionForm(panelState.edit);
    }

    const updateSection = () => {
        
        disabled = true;

        router.postWithToken(`/api/question-bank/update/section/${sectionData.sectionId}`, { sectionTitle: sectionData.sectionTitle, sectionDescription: sectionData.sectionDescription, questionType: sectionData.questionType }, {

            onSuccess : (res) => {
                getSections();
                closeSheet();

                disabled = false;
            },
            onError: (res) => {
                disabled = false
            }
        });
    }

    const deleteSection = (sectionId) => {
        
        router.postWithToken(`/api/question-bank/delete/section/${sectionId}`, { questionBankId }, {

            onSuccess : (res) => {
                getSections();
            }
        });
    }

    const closeSheet = () => {

        sectionData = JSON.parse(initSectionData);
        showAddSectionForm = false

    };

    const openSectionForm = (state) => {
        slideFormState = state;
        slidePanelTitle = state;
        showAddSectionForm = true;
    }

</script>

<Layout>
    <div class="mt-20 flex items-center justify-between border-b bg-white">
        <div class="flex space-x-3 items-center py-4 px-8">
            <Icons icon="chart" className="h-5 w-5" />
            <span class="mx-2 text-sm font-medium text-gray-700">Questions </span>
            <Icons icon="chevron_right" className="h-4 w-4 fill-gray-800" />
            <span class="text-sm font-medium text-gray-700">Create</span>
        </div>
    </div>
    <div class="w-full mx-auto h-full"> 
        <div class="container max-w-4xl my-12 space-x-10">
            <div class="border bg-white p-8 rounded-lg max-w-8xl">
                <div class="flex items-center justify-between my-2 border-b pb-8">
                    <div>
                        <div class="text-lg font-semibold min-w-max text-gray-700">Add Sections</div>
                        <div class="text-gray-400 text-sm">Please add relevant sections below</div>
                    </div>
                    <Button on:click={ () => openSectionForm(panelState.createSection) } buttonText="Add New Section" className="max-w-min min-w-max" />
                </div>
        
                <div class="mt-12 space-y-6 w-full">
                   <Table { headings }>
                    { #each sections as section, index }
                    <tr>
                        <td class="px-4 py-4 text-sm text-gray-500 dark:text-gray-300 whitespace-nowrap">{ index + 1 }</td>
                        <td class="px-4 py-4 text-sm text-gray-500 dark:text-gray-300 whitespace-nowrap">{ section.sectionTitle }</td>
                        <td class="px-4 py-4 text-sm text-gray-500 dark:text-gray-300 whitespace-nowrap">{ section.sectionDescription }</td>
                        <td class="px-4 py-4 flex space-x-2 text-sm text-gray-500 dark:text-gray-300 whitespace-nowrap">
                            <Button on:click={ () => editSection(section) } buttonText="Edit" className="text-xs" />
                            <Button on:click={ () => deleteSection(section.sectionId) } buttonText="Delete" className="text-xs bg-red-500 focus:ring-red-200 hover:bg-red-600 focus:bg-red-500" />
                        </td>
                    </tr>
                { /each }
                   </Table>
                </div>

                <div class="flex w-full mt-10 justify-end">
                    <div class="w-40">
                        <Button on:click={ addSectionsToQuestionBank } buttonText="Continue" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</Layout>

<SlidePanel title={ slidePanelTitle } showSheet={ showAddSectionForm } on:close-button={ closeSheet }>
        <div class="flex flex-col space-y-6 p-3">
            <div>
                <Input on:input={ (e) => sectionData.sectionTitle = e.detail.input } value={ sectionData.sectionTitle } label="Enter Section Title" labelStyle="font-semibold" />
            </div>

            <div>
                <Select on:selected={ (e) => sectionData.questionType = e.detail.value } on:deselected={ (e) => sectionData.questionType = "" } options={ questionTypes } className="text-sm" optionsStyling="text-sm" placeholder="Select Question Type" label="Select Question Type" labelStyle="font-semibold"  />
            </div>

            <div>
                <Input on:input={ (e) => sectionData.sectionDescription = e.detail.input }  value={ sectionData.sectionDescription } label="Enter Section Description"  labelStyle="font-semibold" />
            </div>

            <div class="w-20">
                { #if slideFormState == panelState.edit }
                    <Button { disabled } on:click={ updateSection } buttonText="Update" className="min-w-max max-w-min"/>
                { :else }
                    <Button { disabled } on:click={ createSection } buttonText="Add Section" className="min-w-max max-w-min"/>
                {/if}
               
            </div>
        </div>
</SlidePanel>