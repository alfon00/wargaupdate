{{-- Expects $user (User). Must be outside the profile update form. --}}
@if($user->hasUploadedAvatar())
    <form id="profile-avatar-delete-form" method="POST" action="{{ $user->profileAvatarDestroyRoute() }}" class="lw-sr-only" aria-hidden="true">
        @csrf
        @method('DELETE')
    </form>
@endif
