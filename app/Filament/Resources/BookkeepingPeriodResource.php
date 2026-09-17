<?php

namespace App\Filament\Resources;

use App\Enums\PeriodStatus;
use App\Filament\Forms\BookkeepingForm;
use App\Filament\Resources\BookkeepingPeriodResource\Pages;
use App\Models\BookkeepingPeriod;
use App\Services\BookkeepingPeriodService;
use Filament\Forms\Form;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BookkeepingPeriodResource extends Resource
{
    protected static ?string $model = BookkeepingPeriod::class;

    protected static ?string $navigationIcon = 'tabler-clipboard-list';

    protected static ?string $navigationGroup = 'Pembukuan';

    protected static ?string $navigationLabel = 'Pembukuan Harian';

    protected static ?string $modelLabel = 'Pembukuan';

    protected static ?string $pluralModelLabel = 'Pembukuan Harian';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema(BookkeepingForm::schema($form->getRecord()));
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('Ringkasan')
                ->columns(4)
                ->schema([
                    TextEntry::make('period_date')->label('Tanggal')->date('d F Y'),
                    TextEntry::make('status')->badge(),
                    TextEntry::make('summary.total_units')->label('Unit'),
                    TextEntry::make('notes')->label('Keterangan')->columnSpanFull(),
                    TextEntry::make('summary.normal_revenue')->label('Pendapatan normal')->formatStateUsing(fn ($state) => rupiah($state)),
                    TextEntry::make('summary.additional_income')->label('Tambahan')->formatStateUsing(fn ($state) => rupiah($state)),
                    TextEntry::make('summary.discount')->label('Potongan')->formatStateUsing(fn ($state) => rupiah($state)),
                    TextEntry::make('summary.actual_revenue')->label('Pendapatan aktual')->formatStateUsing(fn ($state) => rupiah($state)),
                    TextEntry::make('summary.worker_cost_total')->label('Biaya pekerja')->formatStateUsing(fn ($state) => rupiah($state)),
                    TextEntry::make('summary.operational_cost')->label('Operasional harian')->formatStateUsing(fn ($state) => rupiah($state)),
                    TextEntry::make('summary.margin')->label('Margin')->formatStateUsing(fn ($state) => rupiah($state)),
                    TextEntry::make('summary.net_profit')->label('Laba bersih harian')->formatStateUsing(fn ($state) => rupiah($state)),
                ]),
            Section::make('Rincian layanan')->schema([
                RepeatableEntry::make('items')
                    ->label('')
                    ->schema([
                        TextEntry::make('vehicleType.name')->label('Kendaraan')->placeholder('Lainnya'),
                        TextEntry::make('service.name')
                            ->label('Layanan')
                            ->formatStateUsing(function ($state, $record) {
                                return $record->custom_name ?: $state;
                            }),
                        TextEntry::make('quantity')->label('Unit'),
                        TextEntry::make('normal_price')->label('Harga snapshot')->formatStateUsing(fn ($state) => rupiah($state)),
                        TextEntry::make('actual_revenue')->label('Pendapatan')->formatStateUsing(fn ($state) => rupiah($state)),
                        TextEntry::make('worker_total')->label('Pekerja')->formatStateUsing(fn ($state) => rupiah($state)),
                    ])
                    ->columns(6),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('period_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')->badge()->sortable(),
                Tables\Columns\TextColumn::make('summary.total_units')->label('Unit')->alignEnd(),
                Tables\Columns\TextColumn::make('summary.actual_revenue')
                    ->label('Omzet')
                    ->alignEnd()
                    ->formatStateUsing(fn ($state) => rupiah($state)),
                Tables\Columns\TextColumn::make('summary.worker_cost_total')
                    ->label('Pekerja')
                    ->alignEnd()
                    ->formatStateUsing(fn ($state) => rupiah($state)),
                Tables\Columns\TextColumn::make('summary.net_profit')
                    ->label('Laba harian')
                    ->alignEnd()
                    ->formatStateUsing(fn ($state) => rupiah($state)),
                Tables\Columns\TextColumn::make('creator.name')->label('Diinput')->toggleable(),
            ])
            ->defaultSort('period_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(PeriodStatus::class),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('finalize')
                    ->label('Finalkan')
                    ->icon('tabler-circle-check')
                    ->color('success')
                    ->visible(fn (BookkeepingPeriod $record) => $record->isDraft())
                    ->requiresConfirmation()
                    ->action(function (BookkeepingPeriod $record) {
                        app(BookkeepingPeriodService::class)->finalize($record, auth()->user());
                        Notification::make()->title('Pembukuan difinalkan')->success()->send();
                    }),
                Tables\Actions\Action::make('lock')
                    ->label('Kunci')
                    ->icon('tabler-lock')
                    ->color('danger')
                    ->visible(fn (BookkeepingPeriod $record) => auth()->user()?->isOwner() && ! $record->isLocked())
                    ->requiresConfirmation()
                    ->action(function (BookkeepingPeriod $record) {
                        app(BookkeepingPeriodService::class)->lock($record, auth()->user());
                        Notification::make()->title('Periode dikunci')->success()->send();
                    }),
                Tables\Actions\Action::make('unlock')
                    ->label('Buka kunci')
                    ->icon('tabler-lock-open')
                    ->visible(fn (BookkeepingPeriod $record) => auth()->user()?->isOwner() && $record->isLocked())
                    ->form([
                        \Filament\Forms\Components\Textarea::make('reason')->label('Alasan')->required(),
                    ])
                    ->action(function (BookkeepingPeriod $record, array $data) {
                        app(BookkeepingPeriodService::class)->unlock($record, auth()->user(), $data['reason']);
                        Notification::make()->title('Kunci periode dibuka')->success()->send();
                    }),
                Tables\Actions\DeleteAction::make()
                    ->using(fn (BookkeepingPeriod $record) => app(BookkeepingPeriodService::class)->delete($record, auth()->user())),
            ])
            ->bulkActions([]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['summary', 'creator']);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBookkeepingPeriods::route('/'),
            'create' => Pages\CreateBookkeepingPeriod::route('/create'),
            'view' => Pages\ViewBookkeepingPeriod::route('/{record}'),
            'edit' => Pages\EditBookkeepingPeriod::route('/{record}/edit'),
        ];
    }
}
