<?php

namespace App\Http\Controllers;

use App\Models\DivisionLeadership;
use Illuminate\Http\Request;
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
        //$this->authorize('viewAny', User::class);
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

        //Paginate
        if ($request->filled('sortBy') && $request->filled('sortOrder')) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderByRaw('CASE WHEN term_end IS NULL THEN 0 ELSE 1 END')
                ->orderBy('term_end', 'desc')
                ->orderBy('term_start', 'desc');
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
        $validatedRequest = $request->validated();
        
        DB::beginTransaction();

        //try{
            $divLead = DivisionLeadership::create($validatedRequest);

            DB::commit();
            return $this->success('Division Leader: ' . $divLead->name . ' Created Successfully',[
                'Division Leader' => new DivisionLeadershipResource($divLead)
            ]);
        // }catch(\Exception $e){
        //     DB::rollBack();
        //     return $this->error('Division Leader could not be created');
        // }
    }

    /**
     * Show Division Leadership Record
     * 
     * Display the specified resource.
     */
    public function show(DivisionLeadership $divisionLeadership)
    {
        try{
            return $this->success('Successfully fetched Division Leadership',[
                'division_leadership' => new DivisionLeadershipResource($divisionLeadership)
            ]);
        }catch(\Exception $e){
            return $this->error('Could not fetch the Division Leadership');
        }
    }

    /**
     * Update Division Leadership Record
     * 
     * Update the specified resource in storage.
     */
    public function update(UpdateDivisionLeadershipRequest $request, DivisionLeadership $divisionLeadership)
    {
        
        DB::beginTransaction();

        try{
            $validatedRequest = $request->validated();
            if($request->boolean('current_term')){
                $divisionLeadership->term_end = null;
                $divisionLeadership->save();
            }
            $divisionLeadership->update($validatedRequest);

            DB::commit();
            return $this->success('Division Leadership Updated successfully',[
                new DivisionLeadershipResource($divisionLeadership)
            ]);

        }catch(\Exception $e){
            DB::rollBack();
            return $this->error('Failed to update Division Leadership');
        }
    }

    /**
     * Delete Division Leadership Record
     * 
     * Remove the specified resource from storage.
     */
    public function destroy(DivisionLeadership $divisionLeadership)
    {
        try {
            DB::beginTransaction();
            
            $divisionLeadership->delete();

            DB::commit();
            return $this->success('Division Leadership Deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('Failed to delete Division Leadership');
        }
    }
}


