{{--
    Approve Request Modal
    Used in: resources/views/circulation/index.blade.php
    Triggered by: openApproveModal() JS function
    Submitted via: hidden #approveRequestForm
--}}

{{-- Modal overlay --}}
<div class="modal-overlay" id="approveRequestModal" style="display:none;">
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="approveModalTitle" style="max-width:520px;">

        {{-- Header --}}
        <div class="modal-header">
            <div>
                <div class="modal-title" id="approveModalTitle">
                    <i class="fas fa-check-circle" style="color:var(--success);margin-right:6px;"></i>Approve Borrow Request
                </div>
                <div style="color:var(--text-light);font-size:13px;margin-top:6px;">Review details before confirming approval.</div>
            </div>
            <button type="button" class="modal-close" onclick="closeApproveModal()">&times;</button>
        </div>

        {{-- Body --}}
        <div class="modal-body">

            {{-- Borrower Info --}}
            <div style="margin-bottom:20px;">
                <div style="font-size:12px;font-weight:700;color:var(--text-light);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:10px;">Borrower Information</div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <div style="font-size:11px;color:var(--text-light);">Name</div>
                        <div style="font-size:14px;font-weight:600;" id="approveModalMemberName">—</div>
                    </div>
                    <div>
                        <div style="font-size:11px;color:var(--text-light);">User ID</div>
                        <div style="font-size:14px;font-weight:600;" id="approveModalMemberId">—</div>
                    </div>
                    <div>
                        <div style="font-size:11px;color:var(--text-light);">Email</div>
                        <div style="font-size:14px;font-weight:600;" id="approveModalMemberEmail">—</div>
                    </div>
                    <div>
                        <div style="font-size:11px;color:var(--text-light);">Type</div>
                        <div style="font-size:14px;font-weight:600;text-transform:capitalize;" id="approveModalMemberType">—</div>
                    </div>
                </div>
            </div>

            {{-- Divider --}}
            <div style="border-top:1px solid rgba(0,0,0,0.06);margin:16px 0;"></div>

            {{-- Request Info --}}
            <div style="margin-bottom:20px;">
                <div style="font-size:12px;font-weight:700;color:var(--text-light);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:10px;">Request Details</div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <div style="font-size:11px;color:var(--text-light);">Request Date</div>
                        <div style="font-size:14px;font-weight:600;" id="approveModalRequestDate">—</div>
                    </div>
                    <div>
                        <div style="font-size:11px;color:var(--text-light);">Due Date</div>
                        <div style="font-size:14px;font-weight:600;" id="approveModalDueDate">—</div>
                    </div>
                </div>
            </div>

            {{-- Divider --}}
            <div style="border-top:1px solid rgba(0,0,0,0.06);margin:16px 0;"></div>

            {{-- Book Info --}}
            <div>
                <div style="font-size:12px;font-weight:700;color:var(--text-light);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:10px;">Book Details</div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <div style="font-size:11px;color:var(--text-light);">Title</div>
                        <div style="font-size:14px;font-weight:600;" id="approveModalBookTitle">—</div>
                    </div>
                    <div>
                        <div style="font-size:11px;color:var(--text-light);">Author</div>
                        <div style="font-size:14px;font-weight:600;" id="approveModalBookAuthor">—</div>
                    </div>
                    <div>
                        <div style="font-size:11px;color:var(--text-light);">Category / Kind</div>
                        <div style="font-size:14px;font-weight:600;" id="approveModalBookCategory">—</div>
                    </div>
                    <div>
                        <div style="font-size:11px;color:var(--text-light);">ISBN / Accession</div>
                        <div style="font-size:14px;font-weight:600;" id="approveModalBookIdentifier">—</div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Footer --}}
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeApproveModal()">Cancel</button>
            <button type="button" class="btn btn-success" onclick="submitApproveModal()">
                <i class="fas fa-check"></i> Confirm Approval
            </button>
        </div>

        {{-- Hidden submission form --}}
        <form id="approveRequestForm" method="POST" style="display:none;" action="">
@csrf
            @method('PATCH')
        </form>
    </div>
</div>

<script>
/**
 * Open the Approve Request modal and populate it with borrower / request / book data.
 *
 * @param {number} txnId               – Transaction (request) ID
 * @param {string} memberName          – Borrower name
 * @param {string} memberId            – Borrower member_id / user ID
 * @param {string} memberEmail         – Borrower email
 * @param {string} memberType          – Borrower type (student / faculty / public)
 * @param {string} requestDate         – Formatted request date
 * @param {string} dueDate             – Formatted due date
 * @param {string} bookTitle           – Book title
 * @param {string} bookAuthor          – Book author
 * @param {string} bookCategory        – Book category name
 * @param {string} bookIsbn            – Book ISBN
 * @param {string} bookAccession       – Book accession number
 */
function openApproveModal(txnId, memberName, memberId, memberEmail, memberType, requestDate, dueDate, bookTitle, bookAuthor, bookCategory, bookIsbn, bookAccession, status, requestStatus) {
    // Populate borrower info
    document.getElementById('approveModalMemberName').textContent     = memberName || '—';
    document.getElementById('approveModalMemberId').textContent       = memberId || '—';
    document.getElementById('approveModalMemberEmail').textContent    = memberEmail || '—';
    document.getElementById('approveModalMemberType').textContent     = memberType || '—';

    // Populate request details
    document.getElementById('approveModalRequestDate').textContent    = requestDate || '—';
    document.getElementById('approveModalDueDate').textContent        = dueDate || '—';

    // Populate book details
    document.getElementById('approveModalBookTitle').textContent      = bookTitle || '—';
    document.getElementById('approveModalBookAuthor').textContent     = bookAuthor || '—';
    document.getElementById('approveModalBookCategory').textContent   = bookCategory || '—';

    // Show ISBN if available, otherwise accession number, otherwise '—'
    const identifier = (bookIsbn && bookIsbn !== '—') ? bookIsbn : (bookAccession && bookAccession !== '—' ? bookAccession : '—');
    document.getElementById('approveModalBookIdentifier').textContent = identifier;

    // Build form action URL - use approveRenew for renew_requested status (PATCH), generic approve otherwise (POST)
    const form = document.getElementById('approveRequestForm');
    let routeName = requestStatus === 'renew_requested' ? 'circulation.approve.renew' : 'circulation.approve';
    form.action = '{{ route("circulation.approve", ["txn" => 0]) }}'.replace('circulation.approve', routeName).replace('/0', '/' + txnId);
    form.method = requestStatus === 'renew_requested' ? 'POST' : 'POST'; // Both are POST per routes, but approve.renew is PATCH - adjust if needed

    // Update modal title for renewal
    document.getElementById('approveModalTitle').innerHTML = requestStatus === 'renew_requested' 
        ? '<i class="fas fa-sync-alt" style="color:var(--success);margin-right:6px;"></i>Approve Renewal Request'
        : '<i class="fas fa-check-circle" style="color:var(--success);margin-right:6px;"></i>Approve Borrow Request';

    // Show modal
    document.getElementById('approveRequestModal').style.display = 'flex';
}

/**
 * Close the Approve Request modal.
 */
function closeApproveModal() {
    document.getElementById('approveRequestModal').style.display = 'none';
}

/**
 * Submit the hidden form to actually POST the approval.
 */
function submitApproveModal() {
    document.getElementById('approveRequestForm').submit();
}

// Close modal when clicking outside the dialog box
document.addEventListener('DOMContentLoaded', function () {
    const overlay = document.getElementById('approveRequestModal');
    if (overlay) {
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) {
                closeApproveModal();
            }
        });
    }
});
</script>

