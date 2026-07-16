<?php

namespace App\Filament\AdminTahfidz\Resources\Students\RelationManagers;

use App\Models\Tahfidz\Rapor;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RaporsRelationManager extends RelationManager
{
    protected static string $relationship = 'rapors';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // TextInput::make('student_id')
                //     ->required()
                //     ->numeric(),
                TextInput::make('tahun_ajaran')
                    ->label('Tahun Ajaran')
                    ->required()
                    ->readOnly(),
                TextInput::make('semester')
                    ->required()
                    ->readOnly(),
                // TextInput::make('category')
                //     ->label('Kategori')
                //     ->readOnly(),
                // TextInput::make('program')
                //     ->label('Program')
                //     ->readOnly(),
                TextInput::make('periodic_score')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->label('Nilai Periodik'),
                TextInput::make('sa_score')
                    ->required()
                    ->minValue(0)
                    ->maxValue(100)
                    ->numeric()
                    ->label('SA Score'),
                TextInput::make('pas_score')
                    ->required()
                    ->minValue(0)
                    ->maxValue(100)
                    ->numeric()
                    ->label('PAS Score'),
                // \Filament\Forms\Components\Toggle::make('pas_succeed')
                //     ->label('PAS Succeed'),
                // \Filament\Forms\Components\Toggle::make('pas_has_customized_juz')
                //     ->label('PAS Has Customized Juz'),
                // \Filament\Forms\Components\Textarea::make('pas_juz_map')
                //     ->label('PAS Juz Map')
                //     ->rows(2),
                // \Filament\Forms\Components\Textarea::make('pas_juz_scores')
                //     ->label('PAS Juz Scores')
                //     ->rows(2),
                // \Filament\Forms\Components\Textarea::make('pas_completed_juz')
                //     ->label('PAS Completed Juz')
                //     ->rows(2),
                // \Filament\Forms\Components\Textarea::make('pas_disabled_juz')
                //     ->label('PAS Disabled Juz')
                //     ->rows(2),
                // TextInput::make('total_juz_pas')
                //     ->numeric()
                //     ->label('Total Juz PAS'),
                TextInput::make('final_score')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->label('Nilai Akhir'),
                Hidden::make('kepala_tahfidz_name'),
                \Filament\Forms\Components\Textarea::make('notes')
                    ->columnSpanFull()
                    ->maxLength(65535),
                TextInput::make('lokasi')
                    ->required()
                    ->maxLength(255),
                \Filament\Forms\Components\DatePicker::make('tanggal')
                    ->required(),

                \Filament\Forms\Components\Select::make('kepala_tahfidz_employee_id')
                    ->label('Kepala Tahfidz')
                    ->options(fn () => \App\Models\Employee::whereHas('positions', function ($q) {
                        $q->where('nama', 'like', 'Kepala-tahfidz%');
                    })->pluck('nama', 'id')->toArray())
                    ->searchable()
                    ->live()
                    ->required()
                    ->afterStateUpdated(
                        function (
                            $state,
                            callable $set
                        ) {
                            $nama = $state ? \App\Models\Employee::find($state)?->nama : null;
                            $set('kepala_tahfidz_name', $nama);
                        }
                    )
                    ->afterStateHydrated(
                        function (
                            $state,
                            callable $set
                        ) {
                            $nama = $state ? \App\Models\Employee::find($state)?->nama : null;
                            $set('kepala_tahfidz_name', $nama);
                        }
                    ),

            ]);
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                return $query->withoutGlobalScopes();
            })
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('tahun_ajaran'),
                TextColumn::make('semester'),
                TextColumn::make('final_score')
                    ->label('Nilai Akhir'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // CreateAction::make(),
                // AssociateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                // DissociateAction::make(),
                // DeleteAction::make(),

                \Filament\Actions\Action::make('editExams')
                    ->label('Edit Exams')
                    ->modalHeading('Edit Exams for this Rapor')
                    ->modalSubmitActionLabel('Save')
                    ->form(fn ($record) => [
                        \Filament\Forms\Components\Repeater::make('examinations')
                            ->label('Examinations')
                            ->grid(fn ($state) => count($state) > 1 ? 2 : 1)
                            ->schema([
                                \Filament\Forms\Components\Hidden::make('is_manually_modified')->default(true),
                                \Filament\Forms\Components\Hidden::make('id'),
                                \Filament\Forms\Components\TextInput::make('juz')->readOnly(),
                                \Filament\Forms\Components\TextInput::make('score')->numeric()->minValue(0)->maxValue(100)->required(),
                                \Filament\Forms\Components\Hidden::make('old_score'),
                                // \Filament\Forms\Components\TextInput::make('old_score')->numeric()->minValue(0)->maxValue(100),
                                // \Filament\Forms\Components\Toggle::make('is_nulled')->label('Tidak Disetor'),
                                // \Filament\Forms\Components\Toggle::make('is_remedialed')->label('Remedial'),
                                // \Filament\Forms\Components\TextInput::make('juz_part')->label('Juz Part'),
                                // \Filament\Forms\Components\TextInput::make('periode')->label('Periode'),
                                // \Filament\Forms\Components\Textarea::make('detail')->label('Detail'),
                                // \Filament\Forms\Components\Toggle::make('is_locked')->label('Terkunci?'),
                            ])
                            // ->addActionLabel('Tambah Examination')
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->columns(2)
                            ->default(
                                fn ($get) => \App\Models\Tahfidz\Examination::where('student_id', $record->student_id)
                                    ->where('tahun_ajaran', $record->tahun_ajaran)
                                    ->where('semester', $record->semester)
                                    ->get()
                                    ->map(fn ($exam) => $exam->only([
                                        'id', 'penguji_id', 'juz', 'score', 'old_score', 'is_nulled', 'is_remedialed', 'juz_part', 'periode', 'detail', 'is_locked',
                                    ]))->sortBy('juz')->toArray()
                            ),
                    ])
                    ->action(function (array $data, $record) {
                        $studentId = $record->student_id;
                        $tahunAjaran = $record->tahun_ajaran;
                        $semester = $record->semester;
                        $idsOnForm = collect($data['examinations'])->pluck('id')->filter()->all();
                        // Update or Create new
                        foreach ($data['examinations'] as $examData) {
                            if (isset($examData['id'])) {
                                dd($examData);
                                \App\Models\Tahfidz\Examination::where('id', $examData['id'])->update(array_merge($examData, [
                                    'student_id' => $studentId,
                                    'tahun_ajaran' => $tahunAjaran,
                                    'semester' => $semester,
                                ]));
                            } else {
                                \App\Models\Tahfidz\Examination::create(array_merge($examData, [
                                    'student_id' => $studentId,
                                    'tahun_ajaran' => $tahunAjaran,
                                    'semester' => $semester,
                                ]));
                            }
                        }
                        // Delete removed
                        $toDelete = \App\Models\Tahfidz\Examination::where('student_id', $studentId)
                            ->where('tahun_ajaran', $tahunAjaran)
                            ->where('semester', $semester)
                            ->when($idsOnForm, fn ($q) => $q->whereNotIn('id', $idsOnForm))
                            ->get();
                        foreach ($toDelete as $deleted) {
                            $deleted->delete();
                        }
                    }),

                \Filament\Actions\Action::make('unduhRapor')
                    ->label('Unduh Rapor')
                    ->url(fn (Rapor $record) => route('rapor-tahfidz', $record->id))
                    ->openUrlInNewTab(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // DissociateBulkAction::make(),
                    // DeleteBulkAction::make(),
                ]),
            ]);
    }
}
