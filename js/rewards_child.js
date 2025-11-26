// rewards_child.js
document.addEventListener('DOMContentLoaded', function () {

    // open modal when redeem clicked
    document.querySelectorAll('.redeem-btn').forEach(btn => {
        btn.addEventListener('click', function (e) {
            const rewardId = this.dataset.rewardId;
            const cost = parseInt(this.dataset.cost, 10);
            const card = this.closest('.reward-card');
            const title = card.querySelector('.card .card-title') ? card.querySelector('.card .card-title').innerText : card.querySelector('.reward-card .card-title') ? card.querySelector('.reward-card .card-title').innerText : card.querySelector('div > div > div').innerText;
            // fallback simple:
            const titleText = card.querySelector('.card-title') ? card.querySelector('.card-title').innerText : card.querySelector('div > div > div').innerText || '';

            openRedeemModal(rewardId, titleText, cost, this);
        });
    });

    // modal confirm button
    const confirmBtn = document.getElementById('confirmRedeemBtn');
    confirmBtn && confirmBtn.addEventListener('click', function () {
        const rewardId = this.dataset.rewardId;
        confirmRedeem(rewardId);
    });

});

function openRedeemModal(rewardId, title, cost, clickedButton) {
    const modal = document.getElementById('redeemModal');
    document.getElementById('modalTitle').innerText = title || 'Confirm Redemption';
    document.getElementById('modalDesc').innerText = `This will cost ${cost} points. Are you sure you want to redeem?`;
    document.getElementById('modalNeedMsg').style.display = 'none';

    // save data on confirm button
    const confirm = document.getElementById('confirmRedeemBtn');
    confirm.dataset.rewardId = rewardId;
    confirm.dataset.cost = cost;

    modal.style.display = 'flex';
}

function closeRedeemModal() {
    const modal = document.getElementById('redeemModal');
    modal.style.display = 'none';
    const confirm = document.getElementById('confirmRedeemBtn');
    confirm.removeAttribute('data-reward-id');
    confirm.removeAttribute('data-cost');
}

function confirmRedeem(rewardId) {
    const confirm = document.getElementById('confirmRedeemBtn');
    const cost = confirm.dataset.cost || 0;

    // prepare form data
    const fd = new FormData();
    fd.append('reward_id', rewardId);
    fd.append('confirm', '1');

    // disable confirm to avoid double submit
    confirm.disabled = true;
    confirm.innerText = 'Processing...';

    fetch('../child/redeem_reward.php', {
        method: 'POST',
        credentials: 'same-origin',
        body: fd
    })
    .then(r => r.json())
    .then(data => {
        confirm.disabled = false;
        confirm.innerText = 'Confirm Redeem';
        if (!data) return;

        if (data.success) {
            // update points badge
            const badge = document.getElementById('myPointsValue');
            if (badge && typeof data.new_points !== 'undefined') {
                badge.innerText = data.new_points;
            }

            // show success toast (simple)
            showToast(data.message || 'Redeemed!');

            // Optionally disable this reward's button (we leave it enabled but visually check affordability)
            document.querySelectorAll(`.redeem-btn[data-reward-id="${rewardId}"]`).forEach(b => {
                b.style.opacity = '0.6';
            });

            closeRedeemModal();

        } else {
            // not enough points -> show message
            if (data.need_more || data.need) {
                const needMsg = document.getElementById('modalNeedMsg');
                needMsg.innerText = `You need ${data.need} more points to redeem this reward.`;
                needMsg.style.display = 'block';
            } else {
                showToast(data.message || 'Could not redeem');
            }
        }
    })
    .catch(err => {
        console.error(err);
        showToast('Server error. Try again.');
        confirm.disabled = false;
        confirm.innerText = 'Confirm Redeem';
    });
}

function showToast(msg) {
    // simple floating toast
    let t = document.createElement('div');
    t.innerText = msg;
    t.style.position = 'fixed';
    t.style.right = '20px';
    t.style.bottom = '24px';
    t.style.background = '#1da1f2';
    t.style.color = '#fff';
    t.style.padding = '10px 14px';
    t.style.borderRadius = '10px';
    t.style.boxShadow = '0 8px 20px rgba(0,0,0,0.12)';
    t.style.zIndex = '99999';
    document.body.appendChild(t);
    setTimeout(() => { t.style.opacity = '0'; setTimeout(()=>t.remove(),400); }, 2200);
}
