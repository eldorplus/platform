<!-- sidebar content -->
<div id="sidebarLeft" class="{{ $sidebarClass }} sidebar mh-column clearfix">
    @yield('sidebarLeft')
    @if(!empty($blocks) && $blocks->get('sidebarLeft'))
        @foreach($blocks->get('sidebarLeft') as $index => $block)
            {!! $block->view !!}
        @endforeach
    @endif
</div>
<!-- end #sidebar-left -->
