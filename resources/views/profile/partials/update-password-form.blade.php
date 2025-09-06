<section>
    <header class="mb-3">
        <h2 class="h5 mb-1">{{ __('Update Password') }}</h2>
        <p class="text-muted small mb-0">{{ __('Ensure your account is using a long, random password to stay secure.') }}</p>
    </header>

    @php($isEntra = isset($user) && strtolower((string)($user->auth_provider ?? '')) === 'entra')
    @if($isEntra)
        <x-adminlte-alert theme="info" title="Información" class="mb-2">
            Usas inicio de sesión con Microsoft Entra. La contraseña se administra en tu proveedor.
        </x-adminlte-alert>
        <fieldset disabled>
            <div class="form-group mb-3">
                <label>{{ __('Current Password') }}</label>
                <input type="password" class="form-control" autocomplete="current-password">
            </div>
            <div class="form-group mb-3">
                <label>{{ __('New Password') }}</label>
                <input type="password" class="form-control" autocomplete="new-password">
            </div>
            <div class="form-group mb-3">
                <label>{{ __('Confirm Password') }}</label>
                <input type="password" class="form-control" autocomplete="new-password">
            </div>
            <button type="button" class="btn btn-secondary" disabled>{{ __('Save') }}</button>
        </fieldset>
    @else
        <form method="post" action="{{ route('password.update') }}" class="mt-3">
            @csrf
            @method('put')

            <div class="form-group mb-3">
                <label for="update_password_current_password">{{ __('Current Password') }}</label>
                <input id="update_password_current_password" name="current_password" type="password" class="form-control @if($errors->updatePassword->has('current_password')) is-invalid @endif" autocomplete="current-password">
                @if($errors->updatePassword->has('current_password'))
                    <div class="invalid-feedback">{{ $errors->updatePassword->first('current_password') }}</div>
                @endif
            </div>

            <div class="form-group mb-3">
                <label for="update_password_password">{{ __('New Password') }}</label>
                <input id="update_password_password" name="password" type="password" class="form-control @if($errors->updatePassword->has('password')) is-invalid @endif" autocomplete="new-password">
                @if($errors->updatePassword->has('password'))
                    <div class="invalid-feedback">{{ $errors->updatePassword->first('password') }}</div>
                @endif
            </div>

            <div class="form-group mb-3">
                <label for="update_password_password_confirmation">{{ __('Confirm Password') }}</label>
                <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="form-control @if($errors->updatePassword->has('password_confirmation')) is-invalid @endif" autocomplete="new-password">
                @if($errors->updatePassword->has('password_confirmation'))
                    <div class="invalid-feedback">{{ $errors->updatePassword->first('password_confirmation') }}</div>
                @endif
            </div>

            <div class="d-flex align-items-center">
                <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                @if (session('status') === 'password-updated')
                    <span class="text-muted small ms-2">{{ __('Saved.') }}</span>
                @endif
            </div>
        </form>
    @endif
</section>
