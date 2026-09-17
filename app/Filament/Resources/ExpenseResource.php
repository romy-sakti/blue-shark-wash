<?php

namespace App\Filament\Resources;

use App\Enums\ExpenseAllocation;
use App\Filament\Resources\ExpenseResource\Pages;
use App\Filament\Support\MoneyInput;
use App\Models\BookkeepingPeriod;
use App\Models\Expense;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    protected static ?string $navigationIcon = 'tabler-receipt-2';

    protected static ?string $navigationGroup = 'Pembukuan';

    protected static ?string $navigationLabel = 'Pengeluaran';

    protected static ?string $modelLabel = 'Pengeluaran';

    protected static ?string $pluralModelLabel = 'Pengeluaran';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\DatePicker::make('expense_date')
                ->label('Tanggal')
                ->required()
                ->native(false)
                ->displayFormat('d F Y')
                ->default(now())
                ->live(),
            Forms\Components\Select::make('allocation')
                ->label('Pembebanan')
                ->options(ExpenseAllocation::class)
                ->required()
                ->default(ExpenseAllocation::Daily)
                ->live()
                ->helperText('Harian masuk laporan tanggal tersebut. Bulanan hanya masuk laporan bulan terkait.'),
            Forms\Components\DatePicker::make('attribution_month')
                ->label('Bulan laporan')
                ->native(false)
                ->displayFormat('F Y')
                ->default(now()->startOfMonth())
                ->visible(fn (Forms\Get $get) => $get('allocation') === ExpenseAllocation::Period->value || $get('allocation') === ExpenseAllocation::Period)
                ->required(fn (Forms\Get $get) => $get('allocation') === ExpenseAllocation::Period->value || $get('allocation') === ExpenseAllocation::Period),
            Forms\Components\Select::make('expense_category_id')
                ->label('Kategori')
                ->relationship('category', 'name', fn ($query) => $query->active()->orderBy('sort_order'))
                ->searchable()
                ->preload()
                ->required()
                ->createOptionForm([
                    Forms\Components\TextInput::make('name')->required(),
                ]),
            Forms\Components\TextInput::make('name')
                ->label('Nama / keterangan')
                ->required()
                ->maxLength(255),
            MoneyInput::make('amount')
                ->label('Nominal')
                ->required(),
            Forms\Components\Textarea::make('notes')
                ->label('Catatan')
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('expense_date')->label('Tanggal')->date('d M Y')->sortable(),
                Tables\Columns\TextColumn::make('allocation')->label('Pembebanan')->badge(),
                Tables\Columns\TextColumn::make('category.name')->label('Kategori')->searchable(),
                Tables\Columns\TextColumn::make('name')->label('Keterangan')->searchable(),
                Tables\Columns\TextColumn::make('amount')->label('Nominal')->alignEnd()->formatStateUsing(fn ($state) => rupiah($state)),
                Tables\Columns\TextColumn::make('attribution_month')->label('Bulan')->date('M Y')->toggleable(),
            ])
            ->defaultSort('expense_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('allocation')->options(ExpenseAllocation::class),
                Tables\Filters\SelectFilter::make('expense_category_id')->label('Kategori')->relationship('category', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function canEdit($record): bool
    {
        if ($record instanceof Expense && $record->bookkeeping_period_id) {
            $period = BookkeepingPeriod::query()->find($record->bookkeeping_period_id);

            if ($period?->isLocked() && ! auth()->user()?->isOwner()) {
                return false;
            }
        }

        return parent::canEdit($record);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'edit' => Pages\EditExpense::route('/{record}/edit'),
        ];
    }
}
