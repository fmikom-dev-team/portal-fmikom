<?php

namespace App\Http\Requests\Trace;

class UpdateJobRequest extends StoreJobRequest
{
    /**
     * Get the validation rules for updating a job listing.
     *
     * Makes required fields nullable to support partial updates.
     */
    public function rules(): array
    {
        $rules = parent::rules();

        // Make required fields nullable for partial updates
        $rules['title']            = 'nullable|string|max:255';
        $rules['description']      = 'nullable|string|max:65535';
        $rules['experience_level'] = 'nullable|in:fresh_graduate,junior,mid_level,senior,internship';
        $rules['location_type']    = 'nullable|in:onsite,remote,hybrid';
        $rules['tipe_kerja']       = 'nullable|in:full_time,part_time,magang,freelance';

        return $rules;
    }
}
