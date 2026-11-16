# Chat widget — manual verification checklist

## Panel & agent flows

- [ ] Log in as agent (is_agent=true) → redirected inside /admin, NOT to login page
- [ ] Log in as agent WITHOUT 2FA configured → forced to 2FA setup screen before accessing panel
- [ ] Agent can see open conversations in Chat Inbox
- [ ] Agent sees visitor typing indicator when visitor types (dots appear within ~3 s, fade after 5 s)
- [ ] Agent can send reply → appears in widget instantly (next poll)
- [ ] Agent reply dispatches Telegram notification (check Telegram test channel)
- [ ] Duplicate telegram_message_id on resend does NOT create duplicate row (UniqueConstraintViolation swallowed)

## Widget — form phase

- [ ] FAB appears on non-admin pages; does NOT appear on /admin/*
- [ ] FAB shows unread badge when there are unread agent messages (background poll)
- [ ] Background poll only fires if last conversation activity < 24 h; no poll for old/closed conversations
- [ ] Clicking FAB loads dialog (~6 kB gz first time); subsequent opens are instant
- [ ] Form shows: Nombre (required), Teléfono (optional), Email (optional), consent checkbox
- [ ] "Seguir por WhatsApp" button is visible in form phase and in out-of-hours / new-conv phases
- [ ] "Seguir por WhatsApp" is NOT a separate floating button — only inside the dialog
- [ ] Honeypot field (website) is hidden; bot submission returns 422
- [ ] Consent unchecked → error shown, form not submitted
- [ ] Form with email → `visitor_token_cipher` stored in DB (needed for email notification)

## Widget — chat phase

- [ ] Sending a message shows optimistic bubble immediately
- [ ] Failed send shows "Reintentar" button; retry works
- [ ] Visitor typing indicator: fast typing sends max 1 POST /typing per 3 s
- [ ] Agent typing dots appear in widget when agent types in panel
- [ ] Double-check marks appear on sent messages; fill blue when agent reads
- [ ] Photo upload resized to max 2000 px, EXIF stripped, progress bar shown
- [ ] Close dialog → FAB visible; reopen → chat resumes from last message

## Mobile

- [ ] Dialog fills full screen (100dvh) on mobile
- [ ] iOS: virtual keyboard pushes dialog up (visualViewport API); input stays visible
- [ ] Font-size 16 px on input/textarea prevents iOS auto-zoom
- [ ] env(safe-area-inset-*) applied — FAB not hidden behind iPhone home bar

## Full flow: mobile ↔ panel ↔ Telegram

- [ ] Visitor opens widget on mobile, fills form (name + email), starts chat
- [ ] "Hola, escribo desde móvil" → appears in Filament panel AND in Telegram
- [ ] Agent replies from panel → message appears in mobile widget within 3 s (active poll)
- [ ] Agent replies from Telegram (/reply command or reply to forwarded message) → message appears in widget
- [ ] Tab title shows unread count when widget is closed and new message arrives

## Email notification

- [ ] Visitor provides email at form → `visitor_token_cipher` populated
- [ ] Agent sends reply, visitor hasn't read in 10 min → email sent with agent message and resume link
- [ ] Resume link (signed, valid 7 days) redirects to original page with `?chat_uuid=&chat_token=#chat`
- [ ] Widget on page load reads URL params, saves to localStorage, opens conversation, cleans URL
- [ ] Second email NOT sent within 1 hour of first
- [ ] Email sent after 1 hour throttle window

## Accessibility

- [ ] Dialog has role="dialog" aria-modal="true"
- [ ] Focus trapped inside dialog (Tab / Shift+Tab cycle)
- [ ] Esc closes dialog; focus returns to FAB
- [ ] New agent messages announced by aria-live="polite"

## Security

- [ ] visitor_token only returned in 201 POST /conversations response
- [ ] visitor_token_cipher NOT returned in any API response (in $hidden)
- [ ] IP stored as SHA-256 hash only (never plaintext)
- [ ] Attachment: only real MIME validated via finfo; re-encoded with GD; served via signed URL
- [ ] HEIC files rejected

## Locale

- [ ] Browser in es → Spanish UI
- [ ] Browser in ca-ES → Catalan UI
- [ ] Browser in en-GB → English UI
- [ ] Browser in fr-FR → French UI
- [ ] Unknown locale (ja) → Spanish fallback
