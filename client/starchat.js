// MIT License

// Copyright (c) 2018-2019 NekoBit

// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation firesult (the "Software"), to deal
// in the Software without restriction, including without limitation the rights
// to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:

// The above copyright notice and this permission notice shall be included in all
// copies or substantial portions of the Software.

// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
// SOFTWARE.

let Starchat = function(token = null) {
	this.token = token;
	this.setToken = function(token) {
		this.token = token;
	}
	this.deleteMessage = function(msgId, callback = null) {
		$.ajax({
			url: "../api/",
			type: 'GET',
			data: {
				'token': this.token,
				'deletemessage': msgId
			},
			success: callback
		});
	}

	// Recieves messages
	this.readMessages = function(conversationId, callback = null) {
		$.ajax({
			url: "../api/",
			type: 'GET',
			data: {
				'token': this.token,
				'readmessages': conversationId,
				'count': 25
			},
			dataType: 'json',
			success: callback
		});
	}

	this.getUserInfo = function(user, callback) {
		$.ajax({
			url: '../api/',
			type: 'GET',
			data: {
				'token': this.token,
				'getuserinfo': user
			},
			dataType: 'json',
			success: callback
		})
	}

	this.sendMessage = function(message, id) {
		$.ajax({
			url: "../api/",
			type: 'GET',
			data: {
				'token': token,
				'sendmessage': message,
				'sendmessageto': id
			},
			dataType: 'json'
		});
	}

	this.addContact = function(contactId, callback = null) {
		$.ajax({
			url: "../api/",
			type: 'GET',
			data: {
				'token': token,
				'addcontact': contactId
			},
			success: callback
		});
	}

	this.getContacts = function(callback = null) {
		$.ajax({
			url: "../api/",
			type: 'GET',
			data: {
				"token": token,
				"getcontacts": "yes"
			},
			dataType: 'json',
			success: callback
		});
	}
}
