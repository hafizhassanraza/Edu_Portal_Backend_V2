<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;

use App\Models\MyClass;
use App\Models\Section;


class ClassController extends Controller
{



    


    public function getSections()
    {

        $sections = Section::all();
        return response()->json([
            'sections' => $sections
        ]);
    }



    public function getClasses()
    {
        $classes = MyClass::with('sections')->get();

        return response()->json([
            'classes' => $classes
        ]);
    }





    public function getClassByID($id)
    {
        $class = MyClass::with('sections')->find($id);
        if (!$class) {
            return response()->json(['error' => 'Class not found.'], 404);
        }
        return response()->json(['class' => $class]);
    }

    public function getSectionByID($id)
    {
        $section = Section::find($id);
        if (!$section) {
            return response()->json(['error' => 'Section not found.'], 404);
        }
        return response()->json(['section' => $section]);
    }

    public function getSectionsByClassID($classId)
    {
        $class = MyClass::with('sections')->find($classId);
        if (!$class) {
            return response()->json(['error' => 'Class not found.'], 404);
        }
        return response()->json(['sections' => $class->sections]);
    }

   
    //------------------------------------------------------------------------------

    public function addSection(Request $request)
    {
        $validator = $this->validateSection($request);
        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        $section = Section::create($validator->validated());

        return response()->json([
            'message' => 'Section Created Successfully!',
            'section' => $section,
        ])->setStatusCode(201);

    }


    public function addClass(Request $request)
    {
        $validator = $this->validateClass($request);
        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        $sectionIds = $request->input('section_ids', []);

        // Optionally, validate that all section_ids exist
        $invalidSections = array_diff($sectionIds, Section::whereIn('id', $sectionIds)->pluck('id')->toArray());
        if (count($invalidSections) > 0) {
            return response()->json(['errors' => ['section_ids' => 'Some section IDs are invalid.']], 422);
        }

        $class = MyClass::create($validator->validated());

        // Attach sections to class_section pivot table
        if (!empty($sectionIds)) {
            $class->sections()->attach($sectionIds);
        }

        // Reload class with sections relationship
        $class->load('sections');

        return response()->json([
            'message' => 'Class Created Successfully!',
            'class' => $class,
        ])->setStatusCode(201);

        
    }





    //------------------------------------------------------------------------------
    //*********************** private validation methods **************************|
    //------------------------------------------------------------------------------

    protected function validateClass(Request $request)
    {
        return Validator::make($request->all(), 
        //Rules
        [
            'name' => 'required|string|max:255|unique:my_classes,name',
            'disc' => 'nullable|string|max:1000',
            'section_ids' => 'sometimes|array',
            'section_ids.*' => 'integer|exists:sections,id',
            
        ],
        //Messages
        [
            'name.required' => 'The class name is required.',
            'name.string' => 'The class name must be a string.',
            'name.max' => 'The class name may not be greater than 255 characters.',
            'name.unique' => 'The class name already exist.',
            'disc.string' => 'The description must be a string.',
            'disc.max' => 'The description may not be greater than 1000 characters.',
            'section_ids.array' => 'The section_ids must be an array.',
            'section_ids.*.integer' => 'Each section_id must be an integer.',
            'section_ids.*.exists' => 'One or more section_id values do not exist.',
        ]);



    }

    protected function validateSection(Request $request)
    {
        return Validator::make($request->all(), 
        //Rules
        [
            'name' => 'required|string|max:255|unique:sections,name',
            'disc' => 'nullable|string|max:1000',
        ],
        //Messages
        [
            'name.required' => 'The section name is required.',
            'name.string' => 'The section name must be a string.',
            'name.max' => 'The section name may not be greater than 255 characters.',
            'name.unique' => 'The section name already exist.',
            'disc.string' => 'The description must be a string.',
            'disc.max' => 'The description may not be greater than 1000 characters.',
        ]);
    }

    
}
