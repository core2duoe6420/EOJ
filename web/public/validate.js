var errorCount = 0;
var validateConfig = [
    {
        name: "username",
        reg: "[0-9a-zA-Z]{6}",
        message: "Please enter the validate username!",
        promptmessage: "Username must contain at least 6 words"
    },
    {
        name: "password",
        reg: "[0-9a-zA-Z]{6}",
        message: "Please enter the validate password!",
        promptmessage: "Password must contain at least 6 words"
    },
    {
        name: "repassword",
        reg: null,
        message: "Please enter the same password!",
        promptmessage: "Password must be same as the before"
    },
    {
        name: "email",
        reg: "^([a-zA-Z0-9_\\-\\.]+)@[a-zA-Z0-9-]+(\\.[a-z0-9-]+)*(\\.[a-z]{2,3})$",
        message: "Please enter the validate email!",
        promptmessage: "Password must be as abc@abc.com"
    },
    {
        name: "birthday",
        reg: "(19|20)[0-9]{2}-[0-9]{1,2}-[0-9]{1,2}",
        message: "Please enter the validate birthday!",
        promptmessage: "Password must be as 1970-01-01"
    },
    {
        name: "notnull",
        reg: null,
        message: "Please enter the validate content!",
        promptmessage: "Password must be filled"
    },
    {
        name: "telphone",
        reg: "1[3,5]{1}[0-9]{1}[0-9]{8}",
        message: "Please enter the validate telphone!",
        promptmessage: "Password must contain at least 11 numbers"
    },
    {
        name: "IdNumber",
        reg: "^[0-9]{6}(19|20)[0-9]{2}[0-9]{2}[0-9]{2}[0-9]{3}([0-9]|x|X)$",
        message: "Please enter the validate Id Number!",
        promptmessage: "Password must contains 18 numbers, include the X or x"
    }
];
 
var errorStyle = [
    {
        name: "error",
        style: "1px solid #F00"
    },
    {
        name: "right",
        style: "1px solid #CCC"
    },
    {
        name: "prompt",
        style: "#666"
    }
];
  
/*
 *    This function must provide:
 *  1.validate input box
 *  2.config ID you defined before
 */
function validateSingle(obj, validateName){
    // find the configId through the name
    var configId = 0;
    for(var i = 0; i < validateConfig.length; i += 1){
        if(validateName == validateConfig[i].name){
            configId = i;
        }
    }
     
    // This can find the warning field
    // IE, FF, Chrome is ok
    var warning = obj.parentNode.nextElementSibling || obj.parentNode.nextSibling;
    if(validateConfig[configId].reg != null){
        var borderStyle = errorStyle[0];
        if(obj.value.match(validateConfig[configId].reg)){
            warning.innerHTML = "";
            if(errorCount > 0){
                errorCount =- 1;
            }
            borderStyle = errorStyle[1];
        } else {
            warning.innerHTML = validateConfig[configId].message;
            warning.style.color = "red";
            errorCount =+ 1;
        }
        obj.style.border = borderStyle.style;
         
    } else {
        var borderStyle = errorStyle[0];
        if(obj.value == null || obj.value == ""){
            warning.innerHTML = validateConfig[configId].message;
            warning.style.color = "red";
            errorCount =+ 1;
        } else {
            warning.innerHTML = "";
            if(errorCount > 0){
                errorCount =- 1;
            }
            borderStyle = errorStyle[1];
        }
        obj.style.border = borderStyle.style;
    }
}
 
/*
 *    Submit validate
 *
 *  Dear user you can change the thief warning style and message
 *
 */
function formSubmit(){
    // find all the input field
    // if the field's value is null give a thief warning and set the errorCount's value isn't 0
    var inputs = document.getElementsByTagName("input");
    for(var i = 0; i < inputs.length; i += 1){
        var onblur = inputs[i].getAttribute("onblur");
        if(onblur != null){
            if(inputs[i].value == "" || inputs[i].value == null){
                var errorType = onblur.split(",")[1].substring(2, onblur.split(",")[1].lastIndexOf(")") - 1);
                var configId = 0;
                for(var j = 0; j < validateConfig.length; j += 1){
                    if(errorType == validateConfig[j].name){
                        configId = j;
                    }
                }
                var warning = inputs[i].parentNode.nextElementSibling || inputs[i].parentNode.nextSibling;
                var borderStyle = errorStyle[0];
                inputs[i].style.border = borderStyle.style;
                warning.innerHTML = validateConfig[configId].message;
                warning.style.color = "red";
                errorCount =+ 1;
            }
        }
    }
     
    if(errorCount > 0){
        var thief = document.getElementById("thief_warning");
        thief.style.color = "red";
        thief.innerHTML = "You must finish all the field...";
        return false;
    } else {
        return true;
    }
}
 
// Validate the password and the repassword
// that means find the previous input
function validateRePassword(obj, validateName){
    // find the configId through the name
    var configId = 0;
    for(var i = 0; i < validateConfig.length; i += 1){
        if(validateName == validateConfig[i].name){
            configId = i;
        }
    }
     
    // This can find the warning field
    var warning = obj.parentNode.nextElementSibling || obj.parentNode.nextSibling;
    // This can find the password
    var password = document.getElementsByName("password")[0].value;
     
    var borderStyle = errorStyle[0];
    if(obj.value == password){
        warning.innerHTML = "";
        if(errorCount > 0){
            errorCount =- 1;
        }
        borderStyle = errorStyle[1];
    } else {
        warning.innerHTML = validateConfig[configId].message;
        warning.style.color = "red";
        errorCount =+ 1;
    }
    obj.style.border = borderStyle.style;
}
 
 
/*
 *    Give the prompt message after the input field
 */
function givePrompt(obj, promptName){
    var configId = 0;
    for(var i = 0; i < validateConfig.length; i += 1){
        if(promptName == validateConfig[i].name){
            configId = i;
        }
    }
     
    var warning = obj.parentNode.nextElementSibling || obj.parentNode.nextSibling;
    warning.innerHTML = validateConfig[configId].promptmessage;
    warning.style.color = errorStyle[2].style;
}
