<footer style="background:var(--bg-card);border-top:1px solid var(--border);margin-top:4rem;padding:3rem 1.5rem 1.5rem">
    <div style="max-width:1200px;margin:0 auto">
        <div class="grid grid-4" style="margin-bottom:2.5rem">
            <!-- Brand -->
            <div>
                <a href="{{ url('/') }}" class="navbar-brand" style="margin-bottom:1rem;display:inline-flex">
                    <div class="brand-icon"><i class="fas fa-briefcase"></i></div>
                    <span>{{ config('app.name') }}</span>
                </a>
                <p style="font-size:.875rem;color:var(--text-secondary);line-height:1.7;margin-bottom:1rem">
                    {{ __('messages.footer_desc') }}
                </p>
                <div style="display:flex;gap:.5rem">
                    @foreach(['facebook'=>'#1877f2','twitter'=>'#1da1f2','linkedin'=>'#0077b5','instagram'=>'#e4405f'] as $net=>$color)
                    <a href="#" style="width:36px;height:36px;border-radius:var(--radius);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;color:var(--text-muted);transition:var(--transition);text-decoration:none"
                       onmouseover="this.style.color='{{ $color }}';this.style.borderColor='{{ $color }}'"
                       onmouseout="this.style.color='var(--text-muted)';this.style.borderColor='var(--border)'">
                        <i class="fab fa-{{ $net }}" style="font-size:.875rem"></i>
                    </a>
                    @endforeach
                </div>
            </div>

            <!-- Quick Links -->
            <div>
                <h4 style="font-weight:700;font-size:.9rem;margin-bottom:1rem;color:var(--text-primary)">{{ __('messages.quick_links') }}</h4>
                <div style="display:flex;flex-direction:column;gap:.5rem">
                    @foreach([
                        ['route'=>'jobs.index',    'label'=>__('messages.browse_jobs')],
                        ['route'=>'companies.index','label'=>__('messages.companies')],
                        ['route'=>'about',          'label'=>__('messages.about_us')],
                        ['route'=>'contact',        'label'=>__('messages.contact_us')],
                    ] as $link)
                    <a href="{{ route($link['route']) }}"
                       style="font-size:.85rem;color:var(--text-secondary);text-decoration:none;transition:var(--transition)"
                       onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-secondary)'">
                        <i class="fas fa-chevron-right" style="font-size:.65rem;margin-right:.375rem"></i>
                        {{ $link['label'] }}
                    </a>
                    @endforeach
                </div>
            </div>

            <!-- For Employers -->
            <div>
                <h4 style="font-weight:700;font-size:.9rem;margin-bottom:1rem;color:var(--text-primary)">{{ __('messages.for_employers') }}</h4>
                <div style="display:flex;flex-direction:column;gap:.5rem">
                    @foreach([
                        ['href'=>route('register',['role'=>'company']), 'label'=>__('messages.post_job')],
                        ['href'=>route('companies.index'),              'label'=>__('messages.browse_companies')],
                        ['href'=>'#',                                   'label'=>__('messages.pricing')],
                    ] as $link)
                    <a href="{{ $link['href'] }}"
                       style="font-size:.85rem;color:var(--text-secondary);text-decoration:none;transition:var(--transition)"
                       onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-secondary)'">
                        <i class="fas fa-chevron-right" style="font-size:.65rem;margin-right:.375rem"></i>
                        {{ $link['label'] }}
                    </a>
                    @endforeach
                </div>
            </div>

            <!-- Newsletter -->
            <div>
                <h4 style="font-weight:700;font-size:.9rem;margin-bottom:1rem;color:var(--text-primary)">{{ __('messages.stay_updated') }}</h4>
                <p style="font-size:.8rem;color:var(--text-muted);margin-bottom:.875rem;line-height:1.6">
                    {{ __('messages.newsletter_desc') }}
                </p>
                <div style="display:flex;gap:.375rem">
                    <input type="email" class="form-control" style="font-size:.8rem"
                           placeholder="{{ __('messages.your_email') }}">
                    <button class="btn btn-primary btn-sm" style="white-space:nowrap;flex-shrink:0">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Bottom Bar -->
        <div style="padding-top:1.5rem;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem">
            <div style="font-size:.8rem;color:var(--text-muted)">
                © {{ date('Y') }} {{ config('app.name') }}. {{ __('messages.all_rights_reserved') }}
            </div>
            <div style="display:flex;gap:1.25rem">
                @foreach(['terms'=>__('messages.terms'),'privacy'=>__('messages.privacy')] as $route=>$label)
                <a href="{{ route($route) }}" style="font-size:.8rem;color:var(--text-muted);text-decoration:none"
                   onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-muted)'">
                    {{ $label }}
                </a>
                @endforeach
            </div>
        </div>
    </div>
</footer>