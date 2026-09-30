@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>メンテナンス設定</h1>

        @if (session('success'))
            <div style="margin-bottom: 16px;">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div style="margin-bottom: 16px;">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.maintenance.update') }}">
            @csrf
            @method('PATCH')

            <div style="margin-bottom: 20px;">
                <label>
                    <input type="radio" name="is_active" value="0"
                        {{ old('is_active', $setting->is_active ? '1' : '0') === '0' ? 'checked' : '' }}>
                    メンテナンスOFF
                </label>

                <br>

                <label>
                    <input type="radio" name="is_active" value="1"
                        {{ old('is_active', $setting->is_active ? '1' : '0') === '1' ? 'checked' : '' }}>
                    メンテナンスON
                </label>
            </div>

            <div id="maintenance-end-time-area" style="margin-bottom: 20px;">
                <label for="ends_at">
                    終了予定時刻
                </label>

                <br>

                <input type="datetime-local" id="ends_at" name="ends_at"
                    value="{{ old('ends_at', $setting->ends_at?->format('Y-m-d\TH:i')) }}">

                <p style="font-size: 13px;">
                    メンテナンスをONにする場合は入力してください。
                </p>
            </div>
            <div style="margin-bottom: 20px;">
                <label for="message">
                    メンテナンスメッセージ
                </label>

                <br>

                <textarea id="message" name="message" rows="4" style="width: 100%; max-width: 600px;">{{ old('message', $setting->message) }}</textarea>
            </div>

            <x-button type="submit" variant="primary">
                設定を保存
            </x-button>
        </form>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const radios = document.querySelectorAll(
                'input[name="is_active"]'
            );

            const endTimeArea = document.getElementById(
                'maintenance-end-time-area'
            );

            const endTimeInput = document.getElementById(
                'ends_at'
            );

            function updateMaintenanceForm() {
                const selected = document.querySelector(
                    'input[name="is_active"]:checked'
                );

                const isActive = selected?.value === '1';

                endTimeArea.style.display =
                    isActive ? 'block' : 'none';

                endTimeInput.required = isActive;
            }

            radios.forEach(function(radio) {
                radio.addEventListener(
                    'change',
                    updateMaintenanceForm
                );
            });

            updateMaintenanceForm();
        });
    </script>
@endsection
