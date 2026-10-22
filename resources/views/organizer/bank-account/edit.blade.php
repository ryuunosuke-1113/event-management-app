@extends('layouts.app')

@section('content')
    <div class="card">
        <h1>振込先口座</h1>

        @if (session('success'))
            <p>{{ session('success') }}</p>
        @endif

        @if ($errors->any())
            <div style="margin-bottom: 16px;">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('organizer.bank-account.update') }}">
            @csrf
            @method('PUT')
            <input type="hidden" name="return_to" value="{{ $returnTo }}">

            <div style="margin-bottom: 16px;">
                <label for="bank_name">銀行名</label><br>

                <input type="text" id="bank_name" name="bank_name"
                    value="{{ old('bank_name', $bankAccount?->bank_name) }}" required>
            </div>

            <div style="margin-bottom: 16px;">
                <label for="branch_name">支店名</label><br>

                <input type="text" id="branch_name" name="branch_name"
                    value="{{ old('branch_name', $bankAccount?->branch_name) }}" required>
            </div>

            <div style="margin-bottom: 16px;">
                <label for="account_type">口座種別</label><br>

                <select id="account_type" name="account_type" required>
                    <option value="ordinary" @selected(old('account_type', $bankAccount?->account_type) === 'ordinary')>
                        普通
                    </option>

                    <option value="current" @selected(old('account_type', $bankAccount?->account_type) === 'current')>
                        当座
                    </option>
                </select>
            </div>

            <div style="margin-bottom: 16px;">
                <label for="account_number">口座番号</label><br>

                <input type="text" id="account_number" name="account_number"
                    value="{{ old('account_number', $bankAccount?->account_number) }}" required>
            </div>

            <div style="margin-bottom: 16px;">
                <label for="account_holder_name">口座名義</label><br>

                <input type="text" id="account_holder_name" name="account_holder_name"
                    value="{{ old('account_holder_name', $bankAccount?->account_holder_name) }}" required>
            </div>

            <x-button type="submit" variant="secondary">
                保存
            </x-button>
        </form>
        @if ($bankAccount)
            <form method="POST" action="{{ route('organizer.bank-account.destroy') }}" style="margin-top: 16px;"
                onsubmit="return confirm('振込先口座を削除しますか？');">
                @csrf
                @method('DELETE')
                <input type="hidden" name="return_to" value="{{ $returnTo }}">

                <x-button type="submit" variant="danger">
                    口座情報を削除
                </x-button>
            </form>
        @endif

    </div>
@endsection
