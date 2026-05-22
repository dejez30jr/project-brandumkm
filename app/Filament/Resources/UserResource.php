<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\Models\Kota;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource {
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Manajemen Pengguna';
    protected static ?string $label = 'Pengguna';
    protected static ?string $pluralLabel = 'Pengguna';

    public static function canAccess(): bool {
        return auth()->user()?->isAdmin();
    }

    public static function canEdit( $record ): bool {
        return auth()->user()?->role === 'admin';
    }

    public static function canDelete( $record ): bool {
        return auth()->user()?->role === 'admin';
    }

    public static function form( Form $form ): Form {
        return $form
        ->schema( [
            Forms\Components\Section::make( 'Informasi Pengguna' )
            ->schema( [
                Forms\Components\TextInput::make( 'name' )
                ->label( 'Nama' )
                ->required()
                ->maxLength( 255 ),
                Forms\Components\TextInput::make( 'email' )
                ->email()
                ->required()
                ->unique( ignoreRecord: true )
                ->maxLength( 255 ),
                Forms\Components\TextInput::make( 'password' )
                ->password()
                ->dehydrateStateUsing( fn ( $state ) => Hash::make( $state ) )
                ->dehydrated( fn ( $state ) => filled( $state ) )
                ->required( fn ( string $context ): bool => $context === 'create' ),
                Forms\Components\Select::make( 'role' )
                ->label( 'Role' )
                ->options( [
                    'admin' => 'Admin',
                    'client' => 'Client',
                    'design' => 'Team Design',
                    'pic_lapangan' => 'PIC Lapangan',
                    'team_pasang' => 'Team Pasang',
                ] )
                ->required(),
                Forms\Components\Select::make('kota_id')
    ->label('Kota')
    ->options(Kota::pluck('nama', 'id'))
    ->searchable()
    ->preload()
    ->native(false)
    ->createOptionForm([
        Forms\Components\TextInput::make('nama') 
            ->label('Kota Baru')
            ->required(),
    ])
    ->createOptionUsing(function (array $data) {
        // Simpan ke database
        $kota = Kota::create([
            'nama' => $data['nama'], // Sesuaikan key dengan field di createOptionForm
        ]);

        // Kembalikan ID-nya agar Select otomatis terisi
        return $kota->id;
    }),
                Forms\Components\Toggle::make( 'is_active' )
                ->label( 'Aktif' )
                ->default( true ),
            ] )->columns( 2 ),
        ] );
    }

    public static function table( Table $table ): Table {
        return $table
        ->columns( [
            Tables\Columns\TextColumn::make( 'name' )
            ->label( 'Nama' )
            ->searchable(),
            Tables\Columns\TextColumn::make( 'email' )
            ->searchable(),
            Tables\Columns\BadgeColumn::make( 'role' )
            ->colors( [
                'danger' => 'admin',
                'warning' => 'client',
                'success' => 'design',
                'primary' => 'pic_lapangan',
            ] ),
            Tables\Columns\TextColumn::make( 'kota.nama' )
            ->label( 'Kota' ),
            Tables\Columns\IconColumn::make( 'is_active' )
            ->label( 'Aktif' )
            ->boolean(),
            Tables\Columns\TextColumn::make( 'created_at' )
            ->dateTime( 'd M Y' )
            ->sortable(),
        ] )
        ->filters( [
            Tables\Filters\SelectFilter::make( 'role' )
            ->options( [
                'admin' => 'Admin',
                'client' => 'Client',
                'design' => 'Team Design',
                'pic_lapangan' => 'PIC Lapangan',
            ] ),
            Tables\Filters\SelectFilter::make( 'kota_id' )
            ->label( 'Kota' )
            ->options( Kota::pluck( 'nama', 'id' ) ),
        ] )
        ->actions( [
            Tables\Actions\EditAction::make()
            ->visible( fn () => auth()->user()?->role === 'admin' ),

            Tables\Actions\DeleteAction::make()
            ->visible( fn () => auth()->user()?->role === 'admin' ),
        ] )
        ->bulkActions( [
            Tables\Actions\BulkActionGroup::make( [
                Tables\Actions\DeleteBulkAction::make()
                ->visible( fn () => auth()->user()?->role === 'admin' ),
            ] ),
        ] );
    }

    public static function getPages(): array {
        return [
            'index' => Pages\ListUsers::route( '/' ),
            'create' => Pages\CreateUser::route( '/create' ),
            'edit' => Pages\EditUser::route( '/{record}/edit' ),
        ];
    }
}
