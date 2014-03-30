// Global validation class
var Validation = {

	isBlank: function(val) {
		if (val.length === 0) {
			return true;
		} else {
			return false;
		}
	},

	validChars: function(val) {

		var regex = /[\w\s]$/;

		if (!val.match(regex)) {
			return false;
		} else {
			return true;
		}
	},

	validPass: function(val) {

		var regex = /[\w\s"'\.\^&#@!~\+=\?:;<>\(\)\*]$/;

		if (!val.match(regex)) {
			return false;
		} else {
			return true;
		}
	},

	validEmail: function(email) {

        var regex = "[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?";

        if (!email.match(regex)) {
            return false;
        } else {
            return true;
       }
    },

	correctPassLen: function(val) {
		if (val.length >= 6) {
			return true;
		} else {
			return false;
		}
	},

	correctUsernameLen: function(val) {
		if (val.length >= 4) {
			return true;
		} else {
			return false;
		}
	}

};