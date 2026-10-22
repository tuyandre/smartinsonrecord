{{-- One node of the family tree; recurses through $node['children']. --}}
@php
  $isBull = $node['kind'] === 'bull';
  $canOpen = $node['known'] && auth()->user()->can($isBull ? 'bulls.view' : 'cows.view');
  $url = $canOpen ? route($isBull ? 'bulls.show' : 'cows.show', $node['tag']) : null;
@endphp
<li>
  <div class="ped-node {{ $isBull ? 'is-bull' : 'is-cow' }} {{ $node['known'] ? '' : 'is-unknown' }} {{ ! empty($isRoot) ? 'is-root' : '' }}">
    @if (! empty($node['role']))<div class="ped-role">{{ $node['role'] }}</div>@endif
    <div class="ped-tag">
      <i class="bi {{ $isBull ? 'bi-gender-male' : 'bi-gender-female' }}"></i>
      @if ($url) <a href="{{ $url }}">{{ $node['tag'] }}</a> @else {{ $node['tag'] }} @endif
    </div>
    @if ($node['known'])
      <div class="ped-name">{{ $node['name'] }}</div>
      <div class="ped-meta">{{ collect([$node['breed'], $node['dob']])->filter()->implode(' · ') }}</div>
    @else
      <div class="ped-meta">not registered</div>
    @endif
  </div>
  @if (! empty($node['children']))
    <ul>
      @foreach ($node['children'] as $child)
        @include('pages.pedigree._node', ['node' => $child, 'isRoot' => false])
      @endforeach
    </ul>
  @endif
</li>
