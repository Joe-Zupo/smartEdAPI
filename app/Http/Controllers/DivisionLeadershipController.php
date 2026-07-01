<?php

namespace App\Http\Controllers;

use App\Models\DivisionLeadership;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Requests\DivisionLeadership\StoreDivisionLeadershipRequest;
use App\Http\Requests\DivisionLeadership\UpdateDivisionLeadershipRequest;
use App\Http\Requests\DivisionLeadership\IndexDivisionLeadershipRequest;
use App\Http\Resources\DivisionLeadershipResource;
use Illuminate\Support\Facades\DB;

class DivisionLeadershipController extends Controller
{
    /**
     * Index Records of Division Leadership 
     * 
     * Display a listing of the resource.
     * 
     */
    public function index(IndexDivisionLeadershipRequest $request)
    {
        $this->authorize('viewAny', DivisionLeadership::class);
        $perPage = $request->get('per_page', 5);
        $sortBy = $request->input('sortBy', 'id');
        $sortOrder = $request->input('sortOrder', 'desc');

        $searchRequest = $request->input('search');

        $request->validated();

        $query = DivisionLeadership::query();
        
        //Query Parameters:
        if($request->has('position')){
            $query->where('position', 'like', '%' . $request->input('position') . '%');
        }

        if ($request->has('oic')){
            $query->where('is_oic', $request->boolean('oic'));
        }

        if($searchRequest){
            $query->where('name', 'like', '%' . $searchRequest .'%');
        }

        //dd($request->all());    
        if ($request->has('current')) {

            if ($request->boolean('current')) {

                $query->whereNull('term_end');

            } else {

                $query->whereNotNull('term_end');
            }
        }
        

        //Paginate
        if ($request->boolean('by_date')) {

            $query
                ->orderByRaw('CASE WHEN term_end IS NULL THEN 0 ELSE 1 END')
                ->orderByDesc('term_start')
                ->orderByDesc('term_end');

        } else {

            $query->orderBy($sortBy, $sortOrder);
        }

        $paginatedDivLeads = $query
            ->paginate($perPage);
        
        if(!$paginatedDivLeads->count()){
            return $this->success('No more division leadership records available');
        }

        $transformedDivLeads = $paginatedDivLeads->map(function($divLead){
            return new DivisionLeadershipResource($divLead);
        });

        $paginaton = $this->paginateReturn($paginatedDivLeads);

        return $this->success('Division Leadership fetched successfully',[
           'division_leadership' => DivisionLeadershipResource::collection($transformedDivLeads),
           'pagination' => $paginaton 
        ]);
    }

    /**
     * Create a Division Leadership Record
     * 
     * Store a newly created resource in storage.
     */
    public function store(StoreDivisionLeadershipRequest $request)
    {
        $this->authorize('create', DivisionLeadership::class);
        $validatedRequest = $request->validated();
        
        DB::beginTransaction();

            $validatedRequest['is_oic'] = $request->boolean('is_oic');
            $validatedRequest['current_term'] = $request->boolean('current_term');
            $validatedRequest['image_path'] = $request->file('image')->store('division-leadership', 'public');
            $divLead = DivisionLeadership::create($validatedRequest);

            DB::commit();
            return $this->success('Division Leader: ' . $divLead->name . ' Created Successfully',[
                'Division Leader' => new DivisionLeadershipResource($divLead)
            ]);
    }

    /**
     * Show Division Leadership Record
     * 
     * Display the specified resource.
     */
    public function show(DivisionLeadership $divisionLeadership)
    {
        $this->authorize('view', DivisionLeadership::class);
        try{
            return $this->success('Successfully fetched Division Leadership',[
                'division_leadership' => new DivisionLeadershipResource($divisionLeadership)
            ]);
        }catch(\Exception $e){
            return $this->error('Division Leadership record not found', 404);
        }
    }

    /**
     * Update Division Leadership Record
     * 
     * Update the specified resource in storage.
     */
    public function update(UpdateDivisionLeadershipRequest $request, DivisionLeadership $divisionLeadership)
    {
        $this->authorize('update', DivisionLeadership::class);
        DB::beginTransaction();

        try{
            $validatedRequest = $request->validated();
            if($request->filled('is_oic')){
                $validatedRequest['is_oic'] = $request->boolean('is_oic');
            }
            if($request->filled('current_term')){
                $validatedRequest['current_term'] = $request->boolean('current_term');
                    if($request->boolean('current_term')){
                    $divisionLeadership->term_end = null;
                    $divisionLeadership->save();
                }
            }
            if ($request->hasFile('image')){
                $validatedRequest['image_path'] = $request->file('image')->store('division-leadership', 'public');
            }

            
            $divisionLeadership->update($validatedRequest);

            DB::commit();
            return $this->success('Division Leadership Updated successfully',[
                new DivisionLeadershipResource($divisionLeadership)
            ]);

        }catch(\Exception $e){
            DB::rollBack();
            return $this->error('Failed to update Division Leadership', 403);
        }
    }

    /**
     * Delete Division Leadership Record
     * 
     * Remove the specified resource from storage.
     */
    public function destroy(DivisionLeadership $divisionLeadership)
    {
        $this->authorize('delete', DivisionLeadership::class);
        try {
            DB::beginTransaction();

            if(!$divisionLeadership){
                DB::rollBack();
                return $this->error('Division Leadership record not found', 404);
            }
            
            $divisionLeadership->delete();

            DB::commit();
            return $this->success('Division Leadership Deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('Failed to delete Division Leadership', 403);
        }
    }

    /**
     * Public Index Division Leadership
     * 
     * Display a listing of the resource for public access.
     */
    public function publicIndex(Request $request)
    {

        $filterPosition = $request->validate(['position' => Rule::in(['Schools Division Superintendent', 'Assistant Schools Division Superintendent'])]);

        if (isset($filterPosition['filter']['position'])) {
            $divisionLeaderships = DivisionLeadership::query()->where('position', $filterPosition['position'])
                ->orderByRaw('CASE WHEN term_end IS NULL THEN 0 ELSE 1 END')
                ->orderBy('term_end', 'desc')
                ->orderBy('term_start', 'desc')
                ->get();
        } else {
            $divisionLeaderships = DivisionLeadership::orderByRaw('CASE WHEN term_end IS NULL THEN 0 ELSE 1 END')
                ->orderBy('term_end', 'desc')
                ->orderBy('term_start', 'desc')
                ->get();
        }

        if ($divisionLeaderships->isEmpty()) {
            return response()->json([
                'message' => 'No division leadership records found',
            ]);
        }

        return response()->json([
            'message' => 'Division leadership records retrieved successfully',
            'data' => [
                'Regional Office' => 'Region III - Central Luzon',
                'Division Office' => 'Mabalacat City',
                'address' => 'P. Burgos ST., Poblacion, Mabalacat City, Pampanga',
                'website' => 'depedmabalacat.org',
                'Schools Division Superintendent' => DivisionLeadershipResource::collection($divisionLeaderships->where('position', 'Schools Division Superintendent')),
                'Assistant Schools Division Superintendent' => DivisionLeadershipResource::collection($divisionLeaderships->where('position', 'Assistant Schools Division Superintendent')),
                'office of the superintendent' => DivisionLeadershipResource::collection($divisionLeaderships),
                'telephone' => '(045) 402-7534',
            ],
        ]);
    }
}


