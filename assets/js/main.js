
function startOTPTimer(durationInSeconds, displayElementId, callback) {
    let timer = durationInSeconds;
    const display = document.getElementById(displayElementId);
    
    const interval = setInterval(() => {
        let minutes = Math.floor(timer / 60);
        let seconds = timer % 60;

        seconds = seconds < 10 ? '0' + seconds : seconds;
        display.textContent = minutes + ":" + seconds;

        if (--timer < 0) {
            clearInterval(interval);
            if (callback) callback(); 
        }
    }, 1000);
    
    return interval;
}


const WalletValidator = {

    validateCreditCard: function(cardNumber, cvv) {
        if (cardNumber.length !== 6 || isNaN(cardNumber)) {
            alert("Số thẻ phải đúng 6 chữ số!");
            return false;
        }
        if (cvv.length !== 3 || isNaN(cvv)) {
            alert("Mã CVV phải đúng 3 chữ số!");
            return false;
        }
        return true;
    },

 
    validateWithdrawAmount: function(amount) {
        if (amount < 50000 || amount % 50000 !== 0) {
            alert("Số tiền rút phải là bội số của 50,000 VND!");
            return false;
        }
        return true;
    }
};


function confirmAction(message, actionCallback) {
    if (confirm(message)) {
        actionCallback();
    }
}


async function fetchReceiverName(phone) {
    if (phone.length >= 10) {
        try {

            const response = await fetch(`get_user_name.php?phone=${phone}`);
            const data = await response.json();
            const nameDisplay = document.getElementById('receiver-name-display');
            
            if (data.success) {
                nameDisplay.textContent = "Người nhận: " + data.full_name;
                nameDisplay.classList.remove('text-danger');
                nameDisplay.classList.add('text-success');
            } else {
                nameDisplay.textContent = "Không tìm thấy người dùng!";
                nameDisplay.classList.add('text-danger');
            }
        } catch (error) {
            console.error("Lỗi lấy thông tin:", error);
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {

    if (document.getElementById('otp-timer')) {
        startOTPTimer(60, 'otp-timer', () => {
            alert("Mã OTP đã hết hạn, vui lòng gửi lại mã mới!");
            document.getElementById('btn-submit-otp').disabled = true;
        });
    }
});