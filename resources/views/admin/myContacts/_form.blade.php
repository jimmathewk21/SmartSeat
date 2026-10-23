@csrf
<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">Core Details</div>
            <div class="card-body">
                <div class="form-row">
                    <div class="col-md-6 form-group">
                        <label>Name *</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $myContact->name ?? '') }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Gender</label>
                        <select name="gender" class="form-control @error('gender') is-invalid @enderror">
                            <option value="">Select</option>
                            <option value="Male" @selected(old('gender', $myContact->gender ?? '') === 'Male')>Male</option>
                            <option value="Female" @selected(old('gender', $myContact->gender ?? '') === 'Female')>Female</option>
                            <option value="Other" @selected(old('gender', $myContact->gender ?? '') === 'Other')>Other</option>
                        </select>
                        @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Stars (0-5)</label>
                        <input type="number" min="0" max="5" name="stars" class="form-control @error('stars') is-invalid @enderror" value="{{ old('stars', $myContact->stars ?? 0) }}" readonly>
                        <small class="form-text text-muted">Auto-calculated from category, experience, key person, key role, and VIP speaker tag.</small>
                    </div>
                </div>

                <div class="form-row">
                    <div class="col-md-4 form-group">
                        <label>Designation</label>
                        <input type="text" name="designation" class="form-control @error('designation') is-invalid @enderror" value="{{ old('designation', $myContact->designation ?? '') }}">
                        @error('designation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Company Name</label>
                        <input type="text" name="company_name" class="form-control @error('company_name') is-invalid @enderror" value="{{ old('company_name', $myContact->company_name ?? '') }}">
                        @error('company_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Category</label>
                        <input type="text" name="category" class="form-control @error('category') is-invalid @enderror" value="{{ old('category', $myContact->category ?? '') }}" list="contact-categories">
                        @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="col-md-4 form-group">
                        <label>Area of Expertise</label>
                        <input type="text" name="specialization" class="form-control @error('specialization') is-invalid @enderror" value="{{ old('specialization', $myContact->specialization ?? '') }}" list="contact-specializations" placeholder="Select or type area of expertise">
                        @error('specialization')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Speaker Tag</label>
                        <input type="text" name="speaker_tag" class="form-control @error('speaker_tag') is-invalid @enderror" value="{{ old('speaker_tag', $myContact->speaker_tag ?? '') }}" list="contact-speaker-tags" placeholder="Speaker / Delegate / VIP">
                        @error('speaker_tag')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Country</label>
                        <input type="text" name="country" class="form-control @error('country') is-invalid @enderror" value="{{ old('country', $myContact->country ?? '') }}" list="contact-countries" placeholder="Select or type country">
                        @error('country')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="col-md-6 form-group">
                        <label>Experience</label>
                        <input type="text" name="experience" class="form-control @error('experience') is-invalid @enderror" value="{{ old('experience', $myContact->experience ?? '') }}" list="contact-experiences" placeholder="Select or type experience">
                        @error('experience')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Key Role</label>
                        <input type="text" name="role" class="form-control @error('role') is-invalid @enderror" value="{{ old('role', $myContact->role ?? '') }}">
                        @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="col-md-6 form-group">
                        <label>Primary Event</label>
                        <input type="text" name="event_name" class="form-control @error('event_name') is-invalid @enderror" value="{{ old('event_name', $myContact->event_name ?? '') }}" list="contact-events">
                        @error('event_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 form-group">
                        <label>All Events <small class="text-muted">(One per line)</small></label>
                        <textarea name="event_names" rows="3" class="form-control @error('event_names') is-invalid @enderror" placeholder="One event per line">{{ old('event_names', isset($myContact) ? implode(PHP_EOL, $myContact->event_list) : '') }}</textarea>
                        @error('event_names')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">Contact Channels</div>
            <div class="card-body">
                <div class="form-row">
                    <div class="col-md-4 form-group">
                        <label>Official Email *</label>
                        <input type="email" name="official_email" class="form-control @error('official_email') is-invalid @enderror" value="{{ old('official_email', $myContact->official_email ?? '') }}" data-my-contact-email data-check-url="{{ route('admin.my-contacts.check-email') }}" data-my-contact-id="{{ $myContact->id ?? '' }}" required>
                        @error('official_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="form-text text-danger" id="official-email-availability-message" data-email-message></small>
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Personal Email</label>
                        <input type="email" name="personal_email" class="form-control @error('personal_email') is-invalid @enderror" value="{{ old('personal_email', $myContact->personal_email ?? '') }}">
                        @error('personal_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Alternate Email</label>
                        <input type="email" name="alternate_email" class="form-control @error('alternate_email') is-invalid @enderror" value="{{ old('alternate_email', $myContact->alternate_email ?? '') }}">
                        @error('alternate_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="col-md-4 form-group">
                        <label>Mobile Number</label>
                        <input type="text" name="mobile_number" class="form-control @error('mobile_number') is-invalid @enderror" value="{{ old('mobile_number', $myContact->mobile_number ?? '') }}">
                        @error('mobile_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 form-group">
                        <label>WhatsApp Number</label>
                        <input type="text" name="whatsapp_number" class="form-control @error('whatsapp_number') is-invalid @enderror" value="{{ old('whatsapp_number', $myContact->whatsapp_number ?? '') }}">
                        @error('whatsapp_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Alternate Mobile</label>
                        <input type="text" name="alternate_mobile_number" class="form-control @error('alternate_mobile_number') is-invalid @enderror" value="{{ old('alternate_mobile_number', $myContact->alternate_mobile_number ?? '') }}">
                        @error('alternate_mobile_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="col-md-6 form-group">
                        <label>LinkedIn Profile</label>
                        <input type="text" name="linkedin_profile" class="form-control @error('linkedin_profile') is-invalid @enderror" value="{{ old('linkedin_profile', $myContact->linkedin_profile ?? '') }}" placeholder="https://www.linkedin.com/in/username">
                        @error('linkedin_profile')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Website</label>
                        <input type="text" name="web_url" class="form-control @error('web_url') is-invalid @enderror" value="{{ old('web_url', $myContact->web_url ?? '') }}" placeholder="https://www.example.com">
                        @error('web_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">Profile</div>
            <div class="card-body">
                <div class="form-group">
                    <label>Picture <small class="text-muted">(Recommended: 600 x 600 px)</small></label>
                    <input type="file" name="picture_file" class="form-control @error('picture_file') is-invalid @enderror" accept=".jpg,.jpeg,.png,.webp">
                    <small class="form-text text-muted">Use a square image for best results. Allowed: JPG, PNG, WEBP. Max size: 5MB.</small>
                    @error('picture_file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                @if(!empty($myContact?->picture_url))
                    <div class="mb-3">
                        <img src="{{ asset(ltrim($myContact->picture_url, '/')) }}" alt="{{ $myContact->name }}" class="img-fluid rounded">
                    </div>
                @endif

                <div class="form-group">
                    <label>Status *</label>
                    <select name="contact_status" class="form-control @error('contact_status') is-invalid @enderror" required>
                        @foreach($statusOptions as $value => $label)
                            <option value="{{ $value }}" @selected(old('contact_status', $myContact->contact_status ?? \App\Models\MyContact::STATUS_DEFAULT) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <small class="form-text text-muted">Only one status can be active at a time. Default means subscribed.</small>
                    @error('contact_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-check mb-3">
                    <input type="hidden" name="is_published" value="0">
                    <input type="checkbox" name="is_published" value="1" class="form-check-input" id="is_published" @checked(old('is_published', $myContact->is_published ?? true))>
                    <label class="form-check-label" for="is_published">Published on frontend</label>
                </div>

                <div class="form-check mb-3">
                    <input type="hidden" name="is_whitelisted" value="0">
                    <input type="checkbox" name="is_whitelisted" value="1" class="form-check-input" id="is_whitelisted" @checked(old('is_whitelisted', $myContact->is_whitelisted ?? false))>
                    <label class="form-check-label" for="is_whitelisted">Key Person</label>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">Frontend Display</div>
            <div class="card-body">
                <p class="text-muted mb-3">Unchecked items stay hidden on the frontend and only the default profile data appears.</p>

                @foreach([
                    'show_email' => 'Email-id',
                    'show_whatsapp' => 'Whatsapp',
                    'show_linkedin' => 'Linked-In',
                    'show_website' => 'Website',
                    'show_category' => 'Category',
                    'show_expertise' => 'Expertise',
                ] as $field => $label)
                    <div class="form-check mb-2">
                        <input type="hidden" name="{{ $field }}" value="0">
                        <input type="checkbox" name="{{ $field }}" value="1" class="form-check-input" id="{{ $field }}" @checked(old($field, $myContact->{$field} ?? true))>
                        <label class="form-check-label" for="{{ $field }}">{{ $label }}</label>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="form-group">
            <button class="btn btn-primary" type="submit">Save</button>
            <a class="btn btn-default" href="{{ route('admin.my-contacts.index') }}">Cancel</a>
        </div>
    </div>
</div>

<datalist id="contact-categories">
    @foreach($categories as $category)
        <option value="{{ $category }}"></option>
    @endforeach
</datalist>
<datalist id="contact-countries">
    @foreach($countries as $country)
        <option value="{{ $country }}"></option>
    @endforeach
</datalist>
<datalist id="contact-specializations">
    @foreach($specializations as $specialization)
        <option value="{{ $specialization }}"></option>
    @endforeach
</datalist>
<datalist id="contact-speaker-tags">
    @foreach($speakerTags as $speakerTag)
        <option value="{{ $speakerTag }}"></option>
    @endforeach
</datalist>
<datalist id="contact-experiences">
    @foreach($experiences as $experience)
        <option value="{{ $experience }}"></option>
    @endforeach
</datalist>
<datalist id="contact-events">
    @foreach($events as $event)
        <option value="{{ $event }}"></option>
    @endforeach
</datalist>

<script>
    (function () {
        const emailInput = document.querySelector('[data-my-contact-email]');
        const emailMessage = document.querySelector('[data-email-message]');
        const submitButton = document.querySelector('form button[type="submit"]');

        if (emailInput && emailMessage && submitButton) {
            let timer = null;
            let pendingController = null;

            const setState = function (isTaken, message) {
                emailInput.setCustomValidity(isTaken ? message : '');
                emailMessage.textContent = message || '';
                emailMessage.className = isTaken ? 'form-text text-danger' : 'form-text text-muted';
                submitButton.disabled = isTaken;
            };

            const checkEmail = function () {
                const email = emailInput.value.trim().toLowerCase();
                if (!email) {
                    if (pendingController) {
                        pendingController.abort();
                        pendingController = null;
                    }
                    setState(false, '');
                    return;
                }

                if (pendingController) {
                    pendingController.abort();
                }

                pendingController = new AbortController();
                const params = new URLSearchParams({
                    email: email,
                    my_contact_id: emailInput.dataset.myContactId || '',
                });

                fetch(`${emailInput.dataset.checkUrl}?${params.toString()}`, {
                    headers: {
                        'Accept': 'application/json',
                    },
                    signal: pendingController.signal,
                })
                    .then(function (response) {
                        if (!response.ok) {
                            throw new Error('Email check failed');
                        }
                        return response.json();
                    })
                    .then(function (data) {
                        const isTaken = Boolean(data.exists);
                        setState(isTaken, data.message || (isTaken ? 'This email is already used by another contact.' : ''));
                    })
                    .catch(function (error) {
                        if (error.name !== 'AbortError') {
                            console.error(error);
                        }
                    });
            };

            emailInput.addEventListener('input', function () {
                clearTimeout(timer);
                timer = setTimeout(checkEmail, 300);
            });

            emailInput.addEventListener('blur', checkEmail);

            if (emailInput.value.trim()) {
                checkEmail();
            }
        }
    })();
</script>
