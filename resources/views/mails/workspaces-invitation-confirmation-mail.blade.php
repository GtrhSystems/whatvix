<x-mail::message>
# Workspace invitation
 
Your are invited to a workspace!

### Account Creadentials:

Email: {{ $user->email }}
Password: 123456

<x-mail::button :url="route('user.workspaces.index')">
View Workspaces
</x-mail::button>
 
Thanks,<br>
{{ config('app.name') }}
</x-mail::message>