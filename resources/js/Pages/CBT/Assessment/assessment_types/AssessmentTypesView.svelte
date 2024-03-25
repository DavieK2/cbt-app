<script>
    import { onMount } from "svelte";
    import Button from "../../../components/button.svelte";
    import DataTable from "../../../components/data_table.svelte";
    import Icons from "../../../components/icons.svelte";
    import Input from "../../../components/input.svelte";
    import SlidePanel from "../../../components/slide_panel.svelte";
    import Layout from "../../../layouts/Layout.svelte";
    import { router } from "../../../../util";


    let headings = ['SN', 'Title','Max Score','Actions']

    let assessmentTypes = []

    let assessmentType = "";
    let maxScore = "";
    let assessmentTypeId;

    let disabled = false;

    onMount( () => {

        getAccessmentTypeList();

    })

    let showCreateSheet = false;

    const closeSheet = () => {
        showCreateSheet = false
        reset();
    }

    const showSheet = () => showCreateSheet = true

    const reset = () => {

        assessmentTypeId = undefined;
        assessmentType = "";
        maxScore = ""

        disabled = false;
    }
    const getAccessmentTypeList = () => {

        router.get('/api/assessment-types', {
            onSuccess : (response) => {
               assessmentTypes = response.data
            }
        })
    }

    const createAssessmentType = () => {

        disabled = true;

        router.post('/api/assessment-type/create', { assessmentType, maxScore }, {
            onSuccess: (response) => {
                
                getAccessmentTypeList();
                closeSheet();
                reset();

                disabled = false;

            },
            onError: (response) => {

                disabled = false;
                // console.log(response)
            }
        })
    }

    const updateAssessmentType = () => {

        disabled = true;

        router.post('/api/assessment-type/update', { assessmentType, assessmentTypeId, maxScore }, {
            onSuccess: (response) => {
                
                getAccessmentTypeList();
                closeSheet();
                reset();

                disabled = false;

            },
            onError: (response) => {

                disabled = false;
                // console.log(response)
            }
        })
    }

    const editAssessmentType = (id, name, score) => {
        assessmentTypeId = id
        assessmentType = name
        maxScore = score
        showSheet();
    }

    const deleteAssessmentType = (id, name) => {
        assessmentTypeId = id
        assessmentType = name
    }

    $: disabled = ( assessmentType.length === 0 || maxScore.length === 0 )
</script>
<Layout>
    <div class="container my-28">
        <div class="flex items-center justify-between">
            <div class="flex space-x-3 items-center">
                <Icons icon="medal" className="h-6 w-6" />
                <span class="mx-2 text-lg font-semibold text-gray-800">Assessment Types</span>
            </div>
            <div>
                <Button on:click={ showSheet } buttonText="New Assessment Type" className="text-sm" />
            </div>
        </div>
      
        <DataTable { headings } >
            { #each assessmentTypes as assessmentType,index(assessmentType.id) }
                <tr>
                    <td class="px-4 py-4 text-sm text-gray-800 dark:text-gray-300 whitespace-nowrap">{ index + 1  }</td>
                    <td class="px-4 py-4 text-sm text-gray-800 dark:text-gray-300 whitespace-nowrap">{ assessmentType.type }</td>
                    <td class="px-4 py-4 text-sm text-gray-800 dark:text-gray-300 whitespace-nowrap">{ assessmentType.max_score }</td>
                    <td class="px-4 py-4 text-sm text-gray-800 dark:text-gray-300 whitespace-nowrap">
                        <div class="flex space-x-3">
                            <Button on:click={ () => editAssessmentType(assessmentType.id, assessmentType.type, assessmentType.max_score) } buttonText="Edit" className="text-xs w-20" />
                            <Button buttonText="Delete" className="text-xs w-20 bg-red-500 hover:bg-red-400 focus:bg-red-500 focus:ring focus:ring-red-500 focus:ring-opacity-50" />
                        </div>
                    </td>
                </tr>
            { /each }
        </DataTable>
    </div>
</Layout>

<SlidePanel title="Create New Assessment Type" showSheet={ showCreateSheet } on:close-button={ closeSheet } >
    <div class="flex flex-col space-y-6 p-3">
        <div>
            <Input value={ assessmentType } on:input={ (e) => assessmentType = e.detail.input }  label="Enter Title" />
        </div>
        <div class="w-40">
            <Input type="number" value={ maxScore }  on:input={ (e) => maxScore = e.detail.input }  label="Enter Maximum Score" />
        </div>
        <div class="w-20">
            { #if assessmentTypeId }
                <Button { disabled } on:click={ updateAssessmentType } buttonText="Update" className="text-sm"/>
            { :else }
                <Button { disabled } on:click={ createAssessmentType } buttonText="Save" className="text-sm"/>
            {/if}
           
        </div>
    </div>
</SlidePanel>