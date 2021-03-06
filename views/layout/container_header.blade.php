<!-- Content Header (Page header) -->
@php
    $menu = admin_repo()->now;
    $__head_title = ['LteAdmin']
@endphp

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>
                    @if(isset($page_info))
                        @if(is_array($page_info))
                            @if(isset($page_info['icon']))
                                <i class="{{$page_info['icon']}}"></i>
                            @elseif($menu->getIcon())
                                <i class="{{$menu->getIcon()}}"></i>
                            @endif
                            {!! __($page_info['head_title'] ?? ($page_info['title'] ?? ($menu->getHeadTitle() ?? ($menu->getTitle() ?? 'Blank page')))) !!}
                        @else
                            @if($menu->getIcon()) <i class="{{$menu->getIcon()}}"></i>  @endif {{__($page_info)}}
                        @endif
                    @else
                        @if($menu->getIcon())
                            <i class="{{$menu->getIcon()}}"></i>
                        @endif
                        {!! __($menu->getHeadTitle() ?? ($menu->getTitle() ?? 'Blank page')) !!}
                    @endif
                </h1>
            </div>
            @php($first = admin_repo()->menuList->first())
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    @if (isset($breadcrumb) && is_array($breadcrumb) && count($breadcrumb))
                        @foreach($breadcrumb as $item)
                            @if (is_array($item))
                                @foreach($item as $i)
                                    <li class="breadcrumb-item {{$loop->last ? '' : 'active'}}">
                                        {!! __($i) !!}
                                        @php($__head_title[] = __($i))
                                    </li>
                                @endforeach
                            @else
                                <li class="breadcrumb-item {{$loop->last ? '' : 'active'}}">
                                    {!! __($item) !!}
                                    @php($__head_title[] = __($item))
                                </li>
                            @endif
                        @endforeach
                    @else
                        @if (admin_repo()->nowParents->count() && $first['id'] !== $menu->getId())
                            <li class="breadcrumb-item active">
                                {!! __($first['title']) !!}
                            </li>
                            @foreach(admin_repo()->nowParents->reverse() as $item)
                                <li class="breadcrumb-item {{$loop->last ? '' : 'active'}}">
                                    {!! __($item['title']) !!}
                                    @php($__head_title[] = __($item['title']))
                                </li>
                            @endforeach
                        @endif
                    @endif
                </ol>
            </div>
        </div>
    </div><!-- /.container-fluid -->
</section>
