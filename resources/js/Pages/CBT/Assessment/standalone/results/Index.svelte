<script>
    import Icons from "../../../../components/icons.svelte";
    import DataTable from "../../../../components/data_table.svelte";
    import Layout from "../../../../layouts/Layout.svelte";
    import Dropdown from "../../../../components/dropdown.svelte";
    import { page } from "@inertiajs/svelte";

    let headings = ['S/N', 'Student Name', 'Grade', 'Actions']
    let results = []
    let assessmentId = $page.props.assessmentId;

    let subjects = [];
    let classes = [];



</script>

<Layout>
    <div class="container">
        <div class="my-28">
            <div class="flex items-center justify-between">
                <div class="flex space-x-3 items-center">
                    <Icons icon="chart" className="h-6 w-6" />
                    <span class="mx-2 text-lg font-medium text-gray-800">Results</span>
                </div>
            </div>
            
            <DataTable { headings }>
                { #each results as assessment, index(assessment.assessmentId) }
                    <tr>
                        <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300 whitespace-nowrap">{ index + 1  }</td>
                        <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300 whitespace-nowrap">{ assessment.title }</td>
                        <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300 whitespace-nowrap">{ assessment.isStandalone }</td>
                        <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300 whitespace-nowrap">{ assessment.assessmentType }</td>
                        <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300 whitespace-nowrap">{ assessment.term }</td>
                        <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300 whitespace-nowrap">{ assessment.session }</td>
                        <td class="px-4 py-4 text-xs text-gray-600 dark:text-gray-300 whitespace-nowrap ">
                            <div class={`px-4 py-2 text-center rounded ${ assessment.status == 'Published' ? 'bg-green-200 text-green-800' : 'bg-red-200 text-red-800' }`}>
                                { assessment.status }
                            </div>
                        </td>
                        <td class="px-4 py-4 text-sm text-gray-800 dark:text-gray-300 whitespace-nowrap">
                            <div class="flex space-x-3">
                                <Dropdown arrowColor="fill-gray-600" placeholder="Actions" className="bg-white border  border-gray-300 text-gray-600">
                                    <div class="flex flex-col">
                                        <!-- <button on:click={ () => viewAssessment(assessment) } class="hover:bg-gray-100 p-3 text-sm rounded transition text-left">View Assessment</button> -->
                                        
                                </div>
                                </Dropdown>
                            </div>
                        </td>
                    </tr>
                {/each}
            </DataTable>
        </div>
   </div>
</Layout>