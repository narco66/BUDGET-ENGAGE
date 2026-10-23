{{-- Bloc transversal : avancement chaîne de la dépense --}}
@props(['engagement'])

@php($workflowSteps = \App\Support\ChaineDepense\DossierWorkflowPresenter::fromEngagement($engagement))

<x-ds.card :title="__('Avancement de la chaîne de la dépense')">
    <x-chaine.workflow-stepper :steps="$workflowSteps" />
</x-ds.card>
