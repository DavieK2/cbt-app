<script>
    import { onMount } from "svelte";
    import { router } from "../../../util";
    import Button from "../../components/button.svelte";
    import DataTable from "../../components/data_table.svelte";
    import Icons from "../../components/icons.svelte";
    import Input from "../../components/input.svelte";
    import Select from "../../components/select.svelte";
    import SlidePanel from "../../components/slide_panel.svelte";
    import Layout from "../../layouts/Layout.svelte";
    import Dropdown from "../../components/dropdown.svelte";
    import CsvImportCard from "../../CBT/components/CsvImportCard.svelte";
    import Importmapper from "../../components/importmapper.svelte";



    let disabled = false;
    let checkedAll = false;

    let headings = ['', 'Name', 'Level', 'Student Code', 'Actions']

    let classes = [];

    let firstName;

    let surname;

    let classId;

    let subjects = [];

    let pic;

    let zipFile;

    let selectedSubjects = [];

    let students = [];

    let slideFormState;

    let slidePanelTitle;

    let showStudentForm

    let importCard;

    let importKey;
    let importHeadings = []

    let importOptions = [
        { placeholder : "First Name", value: "firstName", isSelected: false },
        { placeholder : "Last Name", value: "lastName", isSelected: false },
        { placeholder : "Email", value: "email", isSelected: false },
        { placeholder : "Phone Number", value: "phoneNo", isSelected: false },
        { placeholder : "Student Code", value: "studentCode", isSelected: false },
        { placeholder : "Student ID", value: "studentId", isSelected: false },
        { placeholder : "Program of Study", value: "programOfStudy", isSelected: false },
        { placeholder : "Passport", value: "passport", isSelected: false },
        { placeholder : "Level", value: "level", isSelected: false },
        { placeholder : "Session", value: "session", isSelected: false },
    ];

    let studentData = {
        id : "",
        name : "",
        firstName : "",
        surname : "",
        class : "",
        classId : "",
        studentCode : "",
        photo: ""
    }

    let selectedStudents = [];

    let uploadPic = '';

    const reset = () => {

        studentData = {
            id : "",
            name : "",
            firstName : "",
            surname : "",
            class : "",
            classId : "",
            studentCode : "",
            photo: ""
        }
    }

    const panelState = {
        addNewStudent : "Add New Student",
        upload : "Upload File",
        importStudents : "Import Students Data",
        edit : "Edit Student Info",
        assignSubjects : "Assign Courses",
        massAssignCourses: "Assign Courses To Students"
    }


    onMount(() => {

        getStudents();
        getSubjects();

        router.get('/api/classes', {

            onSuccess : (res) => {
                classes = res.data.flatMap((grade) => [{ placeholder : grade.class_name, value : grade.id }])
            }
        })
    })


    const getStudents = () => {

        router.get('/api/students', {
            onSuccess : (res) => {
                students = res.data
            }
        })
    }

    const getSubjects = () => {

        router.get('/api/subjects', {
            onSuccess : (res) => {
                subjects = res.data
            }
        })
    }

    const createStudent = () => {

        disabled = true ;
        
        router.post('/api/student/create', { profilePic: uploadPic, classId: classId.value, firstName: firstName.value, surname: surname.value, studentCode: studentData.studentCode  }, {
            onSuccess : (res) => {

                getStudents();
                closeSheet();
                disabled = false ;
            }, 
            onError : (res) => {
                disabled = false ;
            }
        })

        
    }

    const editStudent = (student) => {

        studentData.id = student.studentId;
        studentData.firstName = student.firstName;
        studentData.surname = student.surname;
        studentData.classId = student.classId;
        studentData.class = student.class;
        studentData.photo = student.photo
        studentData.studentCode = student.studentCode
        uploadPic = student.photo
        
        showSheet(panelState.edit);
    }

    const updateStudent = () => {

        disabled = true ;

        router.post('/api/student/update', { profilePic: uploadPic, studentId: studentData.id, classId: classId.value, firstName: firstName.value, surname: surname.value, studentCode: studentData.studentCode }, {
            onSuccess : (res) => {
                getStudents();
                closeSheet();
                disabled = false ;
            },
            onError: (res) => {
                
                disabled = false ;
            }
        })
    }

    const uploadStudents = () => {
        
        if( disabled ) return;

        disabled = true;

        let data = new FormData();

        data.append('file', importCard.uploadFile);

        router.postFormWithToken('/api/student/upload', data , {

            onSuccess : (res) => {

                importKey = res.data.key;
                importHeadings = res.data.headings;

                showSheet(panelState.importStudents)

                setTimeout(() => disabled = false, 2000 );
            },
            onError : () => {
                setTimeout(() => disabled = false, 2000 );
            }
        })
    }

    const importStudents = (e) => {

        if( disabled ) return;

        disabled = true;

        let data = new FormData();

        if( zipFile ) data.append('file', zipFile[0]);
        
        data.append('importKey', importKey);
       
        data.append('mappings', JSON.stringify(e.detail) );


        router.postFormWithToken('/api/student/import', data , {
            onSuccess : (res) => {

                getStudents();
                closeSheet();
                disabled = false ;

            },
            onError : (res) => {

                disabled = false ;
            }
        })
    }


    const downloadStudentData = () => {

        router.downloadExcel('/api/students/download',{}, {
            onSuccess : (res) => {
                const temp = window.URL.createObjectURL(new Blob([res.data]));
                const link = document.createElement('a');
                link.href = temp;
                link.setAttribute('download', `STUDENT_DATA.xlsx`);
                document.body.appendChild(link);
                link.click();
            }
        })
    }

    const deleteStudent = () => {

        router.post('/api/student/delete', { studentId: studentData.id }, {
            onSuccess : (res) => {
                getStudents();
                closeSheet();
            }
        })
    }

    const showSheet = (state) => {

        slideFormState = state;

        if(slideFormState === panelState.addNewStudent){
            slidePanelTitle = panelState.addNewStudent
        }

        if(slideFormState === panelState.edit){
            slidePanelTitle = panelState.edit
        }

        if(slideFormState === panelState.assignSubjects){
            slidePanelTitle = panelState.assignSubjects
        }

        if(slideFormState === panelState.massAssignCourses){
            slidePanelTitle = panelState.massAssignCourses
        }

        if(slideFormState === panelState.upload){
            slidePanelTitle = panelState.upload
        }

        if(slideFormState === panelState.importStudents){
            slidePanelTitle = panelState.importStudents
        }

        showStudentForm = true
    };

    const closeSheet = () => {

        showStudentForm = false
        reset()

    };

    const assignSubjects = (index) => {

        studentData.id = students[index].studentId

        router.get('/api/student/assigned-subjects/' + studentData.id, {
            onSuccess : (res) => {
                selectedSubjects = res.data
            }
        })

        showSheet(panelState.assignSubjects);
    }

    const massAssignSubjects = () => {

        disabled = true ;

        let subjectIds = selectedSubjects.flatMap( (subject) => [ subject.subjectId ] );

        router.postWithToken(`/api/students/mass-assign-subjects`, { students: selectedStudents, subjects: subjectIds }, {

            onSuccess : (res) => {

                selectedSubjects = [];
                selectedStudents = [];

                checkedAll = false;
                
                closeSheet();

                disabled = false ;
            },
            onError : (res) => {

                disabled = false ;
            }
        })
       
    }

    $: isSelected = (subjectId) => {
        return selectedSubjects.some((selected) => selected.subjectId == subjectId);
    }

    const selectSubject = (subject) => {

        if(isSelected(subject.subjectId)){
            selectedSubjects = selectedSubjects.filter((selected) => selected.subjectId != subject.subjectId);
            return ;
        }

        selectedSubjects.push(subject);
        selectedSubjects = selectedSubjects;

    }

    const assignSubjectToStudent = () => {

        disabled = true ;

        router.post('/api/student/assign-subjects', { studentId : studentData.id, subjects : selectedSubjects }, {

            onSuccess : (res) => {

                closeSheet();
                disabled = false ;
            },
            onError : (res) => {

                disabled = false ;
            }
        })
    }


    $: if (pic) {

        if( pic[0] ){

            pic = pic[0];

            let preview = document.getElementById('preview');
            let reader = new FileReader();
            
            reader.onload = function(e) {
    
                preview.setAttribute('src', e.target.result );

                preview.style.display = 'flex';

                uploadPic = preview.getAttribute('src');

                studentData.photo = uploadPic

            }

            reader.readAsDataURL(pic); 

        }   
    }

    $: disabled = ( slideFormState === panelState.assignSubjects && selectedSubjects.length === 0 )

    $: checked = (studentId) =>  selectedStudents.some((selected) => selected == studentId);

    const uncheckStudent = (studentId) => {
        selectedStudents = selectedStudents.filter( (student) => studentId != student );
    }

    const checkAll = () => {

        if( checkedAll ){

            selectedStudents = [];

        }else{

            selectedStudents = students.flatMap((student) => [ student.studentId ]);
        }

        checkedAll = ! checkedAll;
    }

</script>

<Layout>
    <div class="container my-28">
        <div class="flex items-center justify-between">
            <div class="flex space-x-3 items-center">
                <Icons icon="badge" className="h-6 w-6" />
                <span class="mx-2 text-lg font-semibold text-gray-800">Students ( { students.length } )</span>
            </div>
            <div class="flex space-x-3">
                <Button on:click={ () => showSheet(panelState.addNewStudent) } buttonText="Add New Student" className="text-sm min-w-max max-w-min" />
                <Button on:click={ () => showSheet(panelState.upload) } buttonText="Import Students" className="text-sm min-w-max max-w-min" />
                <Button on:click={ downloadStudentData } buttonText="Download Students Data" className="text-sm min-w-max max-w-min" />
                <Button on:click={ checkAll } buttonText={ checkedAll ? 'Uncheck All' : 'Check All'} className="text-sm min-w-max max-w-min" />
                <Button on:click={ () => showSheet(panelState.massAssignCourses) } buttonText="Assign Courses" className="text-sm min-w-max max-w-min"  />
            </div>
        </div>
       
        <DataTable { headings } >
            { #each students as student, index }
                <tr>
                    <td class="px-4">
                        <input on:input={ (e) => e.target.checked ? uncheckStudent(student.studentId) : selectedStudents.push(student.studentId) } checked={ checked( student.studentId ) } type="checkbox" value={ student.studentId }  name="" id="">
                    </td>
                    <td class="px-4 py-4 text-sm text-gray-500 dark:text-gray-300 whitespace-nowrap">{ student.name }</td>
                    <td class="px-4 py-4 text-sm text-gray-500 dark:text-gray-300 whitespace-nowrap">{ student.class }</td>
                    <td class="px-4 py-4 text-sm text-blue-500 dark:text-gray-300 whitespace-nowrap">{ student.studentCode }</td>
                    <td class="px-4 py-4 text-sm text-gray-500 dark:text-gray-300 whitespace-nowrap">
                        <div class="w-40">
                            <Dropdown arrowColor="fill-gray-600" placeholder="Actions" className="bg-white border  border-gray-300 text-gray-600">
                                <button on:click={ () => editStudent(student) } class="hover:bg-gray-100 p-3 text-sm rounded transition text-left">Edit Student</button>
                                <button on:click={ () => assignSubjects(index) } class="hover:bg-gray-100 p-3 text-sm rounded transition text-left">Assign Courses</button>
                            </Dropdown>
                        </div>
                    </td>
                </tr>
            { /each }
        </DataTable>
    </div>
</Layout>

<SlidePanel title={ slidePanelTitle } showSheet={ showStudentForm } on:close-button={ closeSheet }>
    { #if ( slideFormState === panelState.addNewStudent ) || ( slideFormState === panelState.edit )  }
        <div class="flex flex-col space-y-6 p-3">
            <div>
                <Input on:input={ (e) => studentData.firstName = e.detail.input } bind:this={ firstName } value={ studentData.firstName } label="Enter Student First Name" />
            </div>

            <div>
                <Input on:input={ (e) => studentData.surname = e.detail.input } bind:this={ surname } value={ studentData.surname } label="Enter Student Surname" />
            </div>

            <div>
                <Input on:input={ (e) => studentData.studentCode = e.detail.input } value={ studentData.studentCode } label="Enter Student Code" />
            </div>

            <div>
                <Select on:selected={ (e) => { studentData.classId = e.detail.value; studentData.class = e.detail.placeholder } } bind:this={ classId } isSelected={ studentData.class ? true : false } value={ studentData.classId } options={ classes } placeholder={ studentData.class ? studentData.class : "Select Level" } label="Select Student Level" className="text-sm"  />
            </div>

            <div class="flex flex-col space-y-4">
                <label for="pic" class="text-sm text-gray-800">Upload Student Photo</label>
                <img alt="" class={`rounded-lg h-60 w-60 mt-4 ${ studentData.photo?.length > 0 ? '' : 'hidden' }`} src={ studentData.photo ? studentData.photo : uploadPic } id="preview">
                <input bind:files={ pic } type="file" accept="image/*" id="pic">
            </div>

            <div class="w-20">
                { #if slideFormState == panelState.edit }
                    <Button { disabled } on:click={ updateStudent } buttonText="Update" className=""/>
                { :else }
                    <Button { disabled } on:click={ createStudent } buttonText="Save" className=""/>
                {/if}
               
            </div>
        </div>
    { /if }

    { #if ( (slideFormState === panelState.assignSubjects) || (slideFormState === panelState.massAssignCourses) ) }
        <div class="flex flex-col space-y-3 p-3">
            <p class="text-gray-800 font-semibold pb-6">Courses</p>

            <div class="space-y-3">
                { #if subjects.length === 0 }
                    <div class="flex w-full py-6">
                        <p class="italic text-gray-500">No courses to add</p>
                    </div>
                { :else }
                    { #each subjects as subject }
                        <div class="flex space-x-2 items-center w-full">
                            <button  on:click={ () => selectSubject(subject) } class={`flex items-center shrink-0 justify-center h-[3.2rem] w-[3.2rem] border-2 ${ isSelected(subject.subjectId) ? 'border-green-700' : 'border-gray-300' } rounded-lg`}>
                                <Icons icon="check" className={`${ isSelected(subject.subjectId) ? "stroke-green-700" : "stroke-gray-300" }`} />
                            </button>
                            <div class="w-full">
                                <button type="button" class={`flex p-3 border-2 ${ isSelected(subject.subjectId) ? 'border-green-700' : 'border-gray-300' }  w-full rounded-lg items-center justify-between space-x-2`}>
                                    <span class={`${ isSelected(subject.subjectId) ? "text-green-700" : "text-gray-400" }`} >{ subject.subjectName }</span>
                                </button>
                            </div>
                        </div>
                    { /each }
                {/if}
            </div>

            <div class="pt-10 w-40">
                {  #if (slideFormState === panelState.assignSubjects) }
                    <Button { disabled } on:click={ assignSubjectToStudent } buttonText="Assign Courses" />
                { /if }
                {  #if (slideFormState === panelState.massAssignCourses) }
                    <Button { disabled } on:click={ massAssignSubjects } buttonText="Assign Courses" />
                { /if }
            </div>
        </div>
    { /if }


    { #if  slideFormState === panelState.upload }
        <CsvImportCard { disabled } bind:this={ importCard } on:on-upload={ uploadStudents } />
    {/if} 

    { #if  slideFormState === panelState.importStudents }
       <Importmapper
        { disabled }

        options={ importOptions }
        headings={ importHeadings }
        on:import={ importStudents }
       >

       <div class="py-8">
            <input bind:files={ zipFile } type="file" accept=".zip" class="border border-gray-200 rounded-lg p-4">
       </div>

       </Importmapper>
       
      
    {/if}
  
</SlidePanel>