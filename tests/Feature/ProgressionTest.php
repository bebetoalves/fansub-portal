<?php

namespace Tests\Feature;

use App\Enums\State;
use App\Filament\Resources\ProgressionResource;
use App\Models\Progression;
use Filament\Pages\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProgressionTest extends TestCase
{
    public static function provideValidation(): array
    {
        return [
            'required fields' => [
                'input' => [
                    'name' => null,
                    'states' => null,
                    'project_id' => null,
                ],
                'errors' => [
                    'name' => 'required',
                    'states' => 'required',
                    'project_id' => 'required',
                ],
            ],
            'max 5 states' => [
                'input' => [
                    'states' => State::getValues(),
                ],
                'errors' => [
                    'states' => 'max',
                ],
            ],
            'unique project' => [
                'input' => [
                    'project_id' => fn () => Progression::factory()->createOne()->project_id,
                ],
                'errors' => [
                    'project_id' => 'unique',
                ],
            ],
        ];
    }

    #[Test]
    public function canRenderPage(): void
    {
        $this->get(ProgressionResource::getUrl())->assertSuccessful();
    }

    #[Test]
    public function canRenderColumns(): void
    {
        $data = Progression::factory(10)->create();

        Livewire::test(ProgressionResource\Pages\ManageProgressions::class)
            ->assertCanSeeTableRecords($data)
            ->assertCanRenderTableColumn('name')
            ->assertCanRenderTableColumn('project.title')
            ->assertCanRenderTableColumn('states')
            ->assertCanRenderTableColumn('created_at')
            ->assertCanRenderTableColumn('updated_at');
    }

    #[Test]
    public function canCreate(): void
    {
        $data = Progression::factory()->makeOne();

        Livewire::test(ProgressionResource\Pages\ManageProgressions::class)
            ->callPageAction(CreateAction::class, [
                'name' => $data->name,
                'states' => $data->states->toArray(),
                'project_id' => $data->project_id,
            ])
            ->assertHasNoPageActionErrors();

        self::assertDatabaseHas(Progression::class, [
            'name' => $data->name,
            'states' => json_encode($data->states),
            'project_id' => $data->project_id,
        ]);
    }

    #[Test]
    public function canEdit()
    {
        $record = Progression::factory()->createOne();
        $data = Progression::factory()->makeOne();

        Livewire::test(ProgressionResource\Pages\ManageProgressions::class)
            ->callTableAction(EditAction::class, $record, [
                'name' => $data->name,
                'states' => $data->states->toArray(),
                'project_id' => $data->project_id,
            ]);

        $record->refresh();
        self::assertEquals($data->name, $record->name);
        self::assertEquals($data->states, $record->states);
        self::assertEquals($data->project_id, $record->project_id);
    }

    #[Test]
    public function canDelete()
    {
        $record = Progression::factory()->createOne();

        Livewire::test(ProgressionResource\Pages\ManageProgressions::class)
            ->callTableAction(DeleteAction::class, $record)
            ->assertHasNoTableActionErrors();

        self::assertModelMissing($record);
    }

    #[Test]
    #[DataProvider(methodName: 'provideValidation')]
    public function createValidation(array $input, array $errors): void
    {
        $data = Progression::factory()->makeOne();

        if (is_callable($input['project_id'] ?? null)) {
            $input['project_id'] = $input['project_id']();
        }

        Livewire::test(ProgressionResource\Pages\ManageProgressions::class)
            ->callPageAction(CreateAction::class, array_merge($data->toArray(), $input))
            ->assertHasPageActionErrors($errors);
    }

    #[Test]
    #[DataProvider(methodName: 'provideValidation')]
    public function editValidation(array $input, array $errors)
    {
        $data = Progression::factory()->makeOne();
        $record = Progression::factory()->createOne();

        if (is_callable($input['project_id'] ?? null)) {
            $input['project_id'] = $input['project_id']();
        }

        Livewire::test(ProgressionResource\Pages\ManageProgressions::class)
            ->callTableAction(EditAction::class, $record, array_merge($data->toArray(), $input))
            ->assertHasTableActionErrors($errors);
    }
}
