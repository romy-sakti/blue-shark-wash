<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuditLogResource\Pages;
use App\Models\AuditLog;
use Filament\Forms;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AuditLogResource extends Resource
{
    protected static ?string $model = AuditLog::class;

    protected static ?string $navigationIcon = 'tabler-history';

    protected static ?string $navigationGroup = 'Pengaturan';

    protected static ?string $navigationLabel = 'Log Aktivitas';

    protected static ?string $modelLabel = 'Log aktivitas';

    protected static ?string $pluralModelLabel = 'Log aktivitas';

    protected static ?string $slug = 'log-aktivitas';

    protected static ?int $navigationSort = 2;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::query()->whereDate('created_at', today())->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Aktivitas hari ini';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('user');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Ringkasan')
                ->columns(3)
                ->schema([
                    Infolists\Components\TextEntry::make('created_at')
                        ->label('Waktu')
                        ->dateTime('d F Y H:i'),
                    Infolists\Components\TextEntry::make('user.name')
                        ->label('Pengguna')
                        ->placeholder('Sistem'),
                    Infolists\Components\TextEntry::make('action')
                        ->label('Aktivitas')
                        ->badge()
                        ->formatStateUsing(fn ($state, AuditLog $record) => $record->activityLabel())
                        ->color(fn ($state, AuditLog $record) => $record->actionColor()),
                    Infolists\Components\TextEntry::make('subject')
                        ->label('Objek')
                        ->state(fn (AuditLog $record) => $record->subjectLabel()),
                    Infolists\Components\TextEntry::make('ip_address')
                        ->label('Alamat IP')
                        ->placeholder('—'),
                    Infolists\Components\TextEntry::make('reason')
                        ->label('Alasan')
                        ->placeholder('—')
                        ->columnSpanFull()
                        ->visible(fn (AuditLog $record) => filled($record->reason)),
                ]),
            Infolists\Components\Section::make('Data sebelum')
                ->schema([
                    Infolists\Components\TextEntry::make('old_values')
                        ->hiddenLabel()
                        ->html()
                        ->formatStateUsing(fn ($state) => view('filament.components.audit-values', ['values' => $state])->render()),
                ])
                ->visible(fn (AuditLog $record) => filled($record->old_values)),
            Infolists\Components\Section::make('Data sesudah')
                ->schema([
                    Infolists\Components\TextEntry::make('new_values')
                        ->hiddenLabel()
                        ->html()
                        ->formatStateUsing(fn ($state) => view('filament.components.audit-values', ['values' => $state])->render()),
                ])
                ->visible(fn (AuditLog $record) => filled($record->new_values)),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('H:i')
                    ->description(fn (AuditLog $record) => tanggal_id($record->created_at, 'd M Y'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pengguna')
                    ->placeholder('Sistem')
                    ->searchable(),
                Tables\Columns\TextColumn::make('action')
                    ->label('Aktivitas')
                    ->badge()
                    ->formatStateUsing(fn ($state, AuditLog $record) => $record->activityLabel())
                    ->color(fn ($state, AuditLog $record) => $record->actionColor()),
                Tables\Columns\TextColumn::make('ringkasan')
                    ->label('Ringkasan')
                    ->state(fn (AuditLog $record) => $record->summary())
                    ->wrap()
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function (Builder $inner) use ($search) {
                            $inner->where('action', 'like', "%{$search}%")
                                ->orWhere('reason', 'like', "%{$search}%")
                                ->orWhere('auditable_type', 'like', "%{$search}%")
                                ->orWhereHas('user', fn (Builder $user) => $user->where('name', 'like', "%{$search}%"));
                        });
                    }),
                Tables\Columns\TextColumn::make('reason')
                    ->label('Alasan')
                    ->limit(40)
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->groups([
                Tables\Grouping\Group::make('created_at')
                    ->label('Hari')
                    ->date()
                    ->getTitleFromRecordUsing(fn (AuditLog $record) => tanggal_id($record->created_at, 'l, d F Y'))
                    ->collapsible(),
            ])
            ->defaultGroup('created_at')
            ->filters([
                Tables\Filters\SelectFilter::make('action')
                    ->label('Aktivitas')
                    ->options([
                        'created' => 'Mencatat',
                        'updated' => 'Mengubah',
                        'deleted' => 'Menghapus',
                        'finalized' => 'Finalkan',
                        'locked' => 'Mengunci',
                        'unlocked' => 'Membuka kunci',
                        'logged_in' => 'Masuk',
                    ]),
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Pengguna')
                    ->relationship('user', 'name'),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Dari')
                            ->native(false)
                            ->displayFormat('d F Y'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Sampai')
                            ->native(false)
                            ->displayFormat('d F Y'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $inner, $date) => $inner->whereDate('created_at', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $inner, $date) => $inner->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->emptyStateHeading('Belum ada aktivitas')
            ->emptyStateDescription('Setiap pencatatan, perubahan, penguncian, dan masuk ke sistem akan muncul di sini, dikelompokkan per hari.');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAuditLogs::route('/'),
            'view' => Pages\ViewAuditLog::route('/{record}'),
        ];
    }
}
