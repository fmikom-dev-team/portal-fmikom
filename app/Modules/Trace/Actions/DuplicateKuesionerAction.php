<?php

namespace App\Modules\Trace\Actions;

use App\Models\Tracer\Kuesioner;
use Illuminate\Support\Facades\DB;

class DuplicateKuesionerAction
{
    /**
     * Deep-clone a kuesioner with all sections, pertanyaans, and opsi jawabans.
     */
    public function execute(Kuesioner $kuesioner): Kuesioner
    {
        return DB::transaction(function () use ($kuesioner) {
            $newKuesioner = $kuesioner->replicate();
            $newKuesioner->judul = $kuesioner->judul.' (Salinan)';
            $newKuesioner->save();

            foreach ($kuesioner->sections as $section) {
                $newSection = $section->replicate();
                $newSection->kuesioner_id = $newKuesioner->id;
                $newSection->save();

                foreach ($section->pertanyaans as $pertanyaan) {
                    $newPertanyaan = $pertanyaan->replicate();
                    $newPertanyaan->section_id = $newSection->id;
                    $newPertanyaan->kuesioner_id = $newKuesioner->id;
                    $newPertanyaan->save();

                    foreach ($pertanyaan->opsiJawabans as $option) {
                        $newOption = $option->replicate();
                        $newOption->pertanyaan_id = $newPertanyaan->id;
                        $newOption->save();
                    }
                }
            }

            return $newKuesioner;
        });
    }
}
