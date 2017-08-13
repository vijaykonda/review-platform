/**

Rinkeby TESTNET
 * Dentacoin Review Platform contract created on July the xth, 2017 by Dentacoin B.V. in the Netherlands
 *
 * For terms and conditions visit https://dentacoin.com
 */

pragma solidity ^0.4.11;


   //Dentacoin token import
   contract dcnToken {
     function transfer(address, uint256) returns (bool) {  }
     function balanceOf(address) constant returns (uint256) {  }
   }
   contract owned {
      address public owner;

      function owned() {
          owner = msg.sender;
      }
      modifier onlyOwner {
          require (msg.sender == owner);
          _;
      }
      function transferOwnership(address newOwner) onlyOwner {
          require (newOwner != 0x0);
          owner = newOwner;
      }
  }

  /**
   * Overflow aware uint math functions.
   */
  contract SafeMath {
    //internals
    function safeMul(uint a, uint b) internal returns (uint) {
      uint c = a * b;
      assert(a == 0 || c / a == b);
      return c;
    }
    function safeSub(uint a, uint b) internal returns (uint) {
      assert(b <= a);
      return a - b;
    }
    function safeAdd(uint a, uint b) internal returns (uint) {
      uint c = a + b;
      assert(c>=a && c>=b);
      return c;
    }
  }




























contract Review is owned, SafeMath {

// Declaring Variables----------------------------------------------------------

  // Address of the DCN token: 0x08d32b0da63e2C3bcF8019c9c5d849d7a9d791e6
  dcnToken public tokenAddress;
  uint256 i = 0;
  uint256 public dcnAmount;
  uint256 public dcnAmountTrusted;
  bytes32 public hashedSecret;


  struct Reviews {
    // date of the review
    uint256 timeStamp;
    address writtenByAddress;
    address writtenForAddress;
    uint256 writtenForID;
    bytes32 content;
    bool trusted;
  }

  mapping (uint256 => Reviews) public reviewID;
  mapping (address => bool) public dentistWhitelist;

  mapping (bytes32 => bool) public hashedSubmitSecrets;
  mapping (bytes32 => bool) public hashedInviteSecret;


  function Review() {
    tokenAddress = dcnToken(0x2Debb13BCF5526e0cF5E3A4E5049100E3F7c2AE5);       // Define Dentacoin token address Rinkeby
    // hash of secret "123" to submit reviews for testing !remove in main net version!
    hashedSubmitSecrets[0x64e604787cbf194841e7b68d7cd28786f6c9a0a3ab9f8b0a0e87cb4387ab0107] = true;
  }

// Setter and Getter -----------------------------------------------------------

    function setDCNAmounts (uint256 _dcnAmount, uint256 _dcnAmountTrusted) onlyOwner {
      dcnAmount = _dcnAmount;
      dcnAmountTrusted = _dcnAmountTrusted;
    }

  //todo: return in functions setzen
    function setInviteSecret(bytes32 _secret) {
      require (dentistWhitelist[msg.sender]);
      require (_secret != "");
      hashedSecret = keccak256(_secret);
      hashedInviteSecret[hashedSecret] = true;
    }

    function setDentistOnWhitelist (address _dentist) onlyOwner {
      require (_dentist != 0x0);
      dentistWhitelist[_dentist] = true;
    }


// Main Functions --------------------------------------------------------------


  function () payable onlyOwner{}

  function submitReview(address _to, uint256 _toID, bytes32 _content, string _submitSecret, bytes32 _inviteSecret) {
    //Check if review comes from actual user of review.dentacoin.com
    require (hashedSubmitSecrets[keccak256(_submitSecret)]);

    // Check if review contains any answers
    //require (_content != "");

    //Check if Dentist is whitelisted
    require (dentistWhitelist[_to]);

    //Remove secret from list after using it once !uncomment in main net version!
    //hashedSubmitSecrets[keccak256(_secret)] = false;

    //Store review details
    reviewID[i].timeStamp = now;
    reviewID[i].writtenByAddress = msg.sender;
    reviewID[i].writtenForAddress = _to;
    reviewID[i].writtenForID = _toID;
    reviewID[i].content = _content;

    //Check if review is trusted by comparing hashed Secret from invite email with db
    hashedSecret = keccak256(_inviteSecret);
    if (hashedInviteSecret[hashedSecret]) {
      reviewID[i].trusted = true;
      tokenAddress.transfer(msg.sender, dcnAmountTrusted);
    } else {
      tokenAddress.transfer(msg.sender, dcnAmount);
    }
    i++;
  }
















  //Dentist section

    //if address true then dentist is whitelisted

/*
    struct Dentist {

    }

    function updateAddress(uint256 _id, address _address) {
      require (dentistWhitelist[_id])
    }

*/










  //Admin section




    /*
      function updatehashedSubmitSecrets(import array){
        for (iterate the array) {
          add to mapping
        }
      }

    */




}