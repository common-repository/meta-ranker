jQuery("document").ready(function ($) {
  function debounce(func, wait) {
      var timeout;
      return function () {
        var context = this,
          args = arguments;
        var later = function () {
          timeout = null;
          func.apply(context, args);
        };

        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
      };
  }

  async function getChainId() {
    const chainId = await ethereum.request({ method: "eth_chainId" });
    return parseInt(chainId, 16);
  }
  
  function showPopup(pophtml, VoteType, ListID, ItemName) {
    Swal.fire({
      allowOutsideClick: false,
      html: pophtml,
      customClass: {
        container: "mrv_main_popup_wrap",
        popup: "mrv_popup",
      },
      showCloseButton: true,
      showConfirmButton: false,
      didOpen: () => {
        var wallet_selector = Swal.getPopup().querySelectorAll(".mrv-wallet");
        jQuery(wallet_selector).click(function (evt) {
          let current_wallet = $(this).attr("id");
          mrv_wallets(current_wallet, VoteType, ListID, ItemName);
        });
      },
    });
  }

  
  
  async function switchToMainnet() {
    try {
      await ethereum.request({
        method: "wallet_switchEthereumChain",
        params: [{ chainId: "0x1" }],
      });
    } catch (error) {
      console.log(error);
    }
  }
  
  function handleSuccessResponse(data) {
    let vote = data.data.votes;
    let id = data.data.id;
    jQuery.each(id, function (key, val) {
      let votess = val.votes == "0" ? "" : val.votes;
      jQuery("#mrv_total_votes_" + val.ids).html(
        votess > 0 ? "+" + votess : votess
      );
    });

    let message = data.data.updated == "updated" ? "Vote Updated Successfully" : "Voted Successfully";
    mrv_alert_msg(message, "success", false);

    setTimeout(function () {
      location.reload();
    }, 1000);
  }

  function makeAjaxCall(request_data) {
    jQuery.ajax({
      type: "post",
      dataType: "json",
      url: wallets_data.ajax,
      data: request_data,
      success: function (data) {
        if (data.status == "success") {
          handleSuccessResponse(data);
        } else {
          mrv_alert_msg(data.data, "error", false);
        }
      },
      error: function (XMLHttpRequest, textStatus, errorThrown) {
        console.log("Status: " + textStatus + "Error: " + errorThrown);
      },
    });
  }
  
  function isWalletEnabled(wallet) {
    return (wallet !== undefined && wallet !== "") ? wallet : "1";
  }
  
  function buildPopHtml(wallets_data) {
    let MetaMaskEnable = isWalletEnabled(wallets_data.wallets_enable.metamask_wallet);
    let BinanceEnable = isWalletEnabled(wallets_data.wallets_enable.binance_wallet);
    let WalletCEnable = isWalletEnabled(wallets_data.wallets_enable.wallet_connect);
    
    let pophtml = 
      '<div class="mrv-connector-modal"><div class="mrv-modal-header">Connect Wallet</div><div class="mrv-modal-text">Please connect your wallet to continue.<br>Our System supports the following wallets</div><div class="mrv-modal-content" ><ul class="mrv-wallets" >';
  
    if (MetaMaskEnable == "1") {
      pophtml +=
        '<li class="mrv-wallet" id="metamask_wallet">' +
        '<div class="mrv-wallet-icon"><img src="' + wallets_data.url + 'assets/images/images/metamask.png" alt="metamask"></div>' +
        '<div class="mrv-wallet-title">' + wallets_data.const_msg.metamask_wallet + "</div>" +
        '<svg height="32" width="32" fill="#545454" viewBox="0 0 256 256" id="Flat" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M96,212a4,4,0,0,1-2.82861-6.82837L170.34326,128,93.17139,50.82837a4.00009,4.00009,0,0,1,5.65722-5.65674l80,80a4,4,0,0,1,0,5.65674l-80,80A3.98805,3.98805,0,0,1,96,212Z"></path> </g></svg>'; +
        "</li>";
    }
    if (BinanceEnable == "1") {
      pophtml +=
        '<li class="mrv-wallet" id="Binance_wallet">' +
        '<div class="mrv-wallet-icon"><img src="' + wallets_data.url + 'assets/images/images/binance.jpg" alt="metamask"></div>' +
        '<div class="mrv-wallet-title">' + wallets_data.const_msg.binance_wallet + "</div>" +
        '<svg height="32" width="32" fill="#545454" viewBox="0 0 256 256" id="Flat" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M96,212a4,4,0,0,1-2.82861-6.82837L170.34326,128,93.17139,50.82837a4.00009,4.00009,0,0,1,5.65722-5.65674l80,80a4,4,0,0,1,0,5.65674l-80,80A3.98805,3.98805,0,0,1,96,212Z"></path> </g></svg>'; +
        "</li>";
    }
    if (WalletCEnable == "1") {
      pophtml +=
        '<li class="mrv-wallet" id="wallet_connect">' +
        '<div class="mrv-wallet-icon"><img src="' + wallets_data.url + 'assets/images/images/walletconnect.png" alt="metamask"></div>' +
        '<div class="mrv-wallet-title">' + wallets_data.const_msg.wallet_connect + "</div>" +
        '<svg height="32" width="32" fill="#545454" viewBox="0 0 256 256" id="Flat" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M96,212a4,4,0,0,1-2.82861-6.82837L170.34326,128,93.17139,50.82837a4.00009,4.00009,0,0,1,5.65722-5.65674l80,80a4,4,0,0,1,0,5.65674l-80,80A3.98805,3.98805,0,0,1,96,212Z"></path> </g></svg>'; +
        "</li>";
    }
  
    pophtml += "</ul></div></div>";
    return pophtml;
  }
  
  
  function handleClickEvent(e) {
    var debouncedClickHandler = debounce(async function (e) {
      let VoteType = $(this).attr("data-Vtype")
      let ListID = $(this).attr("data-ListID")
      let ItemName = $(this).attr("data-ItemName")

      if (document.cookie.includes("metaSessionId=")) {
        const metaSessionId = getCookie("metaSessionId");
        var request_data = {
          action: "mrv_skip_wallet",
          vote_type: VoteType,
          current_url: wallets_data.current_url,
          ListID: ListID,
          metaSessionId: metaSessionId,
          ItemName: ItemName,
        };

        makeAjaxCall(request_data);
      } else {
        let pophtml = buildPopHtml(wallets_data);
        try {
          const currentChainId = await getChainId();

          if (!networkInfo.testnets.includes(currentChainId)) {
            showPopup(pophtml, VoteType, ListID, ItemName);
          } else {
            mrv_alert_msg("Please switch to mainnet to vote.", "error", 2000);
            await switchToMainnet();
            showPopup(pophtml, VoteType, ListID, ItemName);
          }
        } catch (error) {
            console.error("Error encountered:", error);
        }
      }
    }, 250);
    $(".mrv-vote-btn").on("click", debouncedClickHandler);
  }
  handleClickEvent();
});

let mrv_wallets = async (wallet_id, VoteType, ListID, ItemName) => {
  var wallet_connect = "";
  var wallet_links = "";
  var wallet_object = "";
  let wallet_type = "";
  const EnableWconnect = mrv_get_widnow_size();
  switch (wallet_id) {
    case "metamask_wallet":
      wallet_type = wallets_data.const_msg.metamask_wallet;
      if (EnableWconnect == true) {
        wallet_object = await mrv_wallet_connect(wallet_type, wallet_id);
      } else {
        wallet_object = window.ethereum;
      }
      wallet_links =
        "https://chrome.google.com/webstore/detail/metamask/nkbihfbeogaeaoehlefnkodbefgpgknn";
      break;
    case "Binance_wallet":
      wallet_type = wallets_data.const_msg.binance_wallet;
      if (EnableWconnect == true) {
        wallet_object = await mrv_wallet_connect(wallet_type, wallet_id);
      } else {
        wallet_object = window.BinanceChain;
      }
      wallet_links =
        "https://chrome.google.com/webstore/detail/binance-wallet/fhbohimaelbohpjbbldcngcnapndodjp";
      break;
    case "wallet_connect":
      wallet_type = wallets_data.const_msg.wallet_connect;
      wallet_object = await mrv_wallet_connect(wallet_type, wallet_id);
      wallet_links = "";
      break;
  }

  if (
    (wallet_id == "wallet_connect" &&
      (wallets_data.infura_id == undefined || wallets_data.infura_id == "")) ||
    (EnableWconnect == true &&
      (wallets_data.infura_id == undefined || wallets_data.infura_id == ""))
  ) {
    mrv_alert_msg(wallets_data.const_msg.infura_msg, "warning", false);
  } else if (typeof wallet_object === "undefined" || wallet_object == "") {
    const el = document.createElement("div");
    el.innerHTML =
      '<a href="' +
      wallet_links +
      '" target="_blank">Click Here </a> to install ' +
      wallet_name +
      " extention";


    Swal.fire({
      title: wallet_name + wallets_data.const_msg.extention_not_detected,
      customClass: { container: "mrv_main_popup_wrap", popup: "mrv_popup" },
      html: el,
      icon: "warning",
    });
  } else {
    const provider = new ethers.providers.Web3Provider(wallet_object, "any");
    const network = await provider.getNetwork();
    let accounts = await provider.listAccounts();
    if (accounts.length == 0) {
      Swal.fire({
        text: wallets_data.const_msg.connection_establish,
        customClass: { container: "mrv_main_popup_wrap", popup: "mrv_popup" },
        didOpen: () => {
          Swal.showLoading();
        },

        allowOutsideClick: false,
      });
      await provider
        .send("eth_requestAccounts", [])
        .then(function (account_list) {
            // console.log(account_list)
          accounts = account_list;
          Swal.close();
        })
        .catch((err) => {
          // console.log(err)
          mrv_alert_msg(
            wallets_data.const_msg.user_rejected_the_request,
            "error",
            2000
          );
        });
    }
    if (accounts.length) {
      const chainId = await ethereum.request({ method: "eth_chainId" });
      const currentChainId = parseInt(chainId, 16);
      if(!networkInfo.testnets.includes(currentChainId)){
        const account = accounts[0];
        const balance = ethers.utils.formatEther(
          await provider.getBalance(accounts[0])
        );
        var request_data = {
          action: "mrv_check_voted_alredy",
          nonce: wallets_data.nonce,
          sender_account: account,
          balance: balance,
          ListID: ListID,
          vote_type: VoteType,
          ItemName: ItemName,
        };
        jQuery.ajax({
          type: "post",
          dataType: "json",
          url: wallets_data.ajax,
          data: request_data,
          success: function (data) {
            if (data.status == "success") {
              if (data.data.updated == "updated") {
                let vote = data.data.votes;
                let id = data.data.id;
          
                jQuery.each(id, function (key, val) {
                  let votess = val.votes == "0" ? "" : val.votes;
                  jQuery("#mrv_total_votes_" + val.ids).html(
                    votess > 0 ? "+" + votess : votess
                  );
                });
                mrv_alert_msg("Vote Updated Successfully", "success", false);
                setTimeout(function () {
                  location.reload();
                }, 1000);
              } else {
                if (data.data.user_id != undefined && data.data.user_id == "") {
                  MrvExtentionCall(
                    account,
                    balance,
                    provider,
                    wallet_id,
                    VoteType,
                    ListID,
                    ItemName,
                    wallet_type
                  );
                }
              }
            } else if (data.status == "error") {
              mrv_alert_msg("Something Went Wrong Please Try Again", "error", false);
            }
          },
          error: function (XMLHttpRequest, textStatus, errorThrown) {
            console.log("Status: " + textStatus + "Error: " + errorThrown);
          },
        });
        // MrvExtentionCall(account, provider, wallet_id, VoteType, ListID, ItemName)
      } else{
        mrv_alert_msg(
          "Please switch to mainnet to vote.",
          "error",
          2000
        );
        try {
          await ethereum.request({
            method: "wallet_switchEthereumChain",
            params: [{ chainId: "0x1" }],
          });
        } catch(error) {
          console.log(error);
        }
        Swal.fire({
          allowOutsideClick: false,
          html: pophtml,
          customClass: {
            container: "mrv_main_popup_wrap",
            popup: "mrv_popup",
          },
          showCloseButton: true,
          showConfirmButton: false,
          didOpen: () => {
            var wallet_selector =
              Swal.getPopup().querySelectorAll(".mrv-wallet");

            jQuery(wallet_selector).click(function (evt) {
              let current_wallet = $(this).attr("id");

              mrv_wallets(current_wallet, VoteType, ListID, ItemName);
            });
          },
        });
      }
    }
  }

};
async function MrvExtentionCall(account, balance, provider, wallet_id, VoteType, ListID, ItemName, wallet_type) {
  try {
      const signer = provider.getSigner();
      const msg = "Sign this request";
      const messageHash = ethers.utils.id(msg);
      const messageHashBytes = ethers.utils.arrayify(messageHash);
      const EnableWconnect = mrv_get_widnow_size();
      const vote_msg = (wallet_id === "wallet_connect" || EnableWconnect || wallet_id === "Binance_wallet")
          ? messageHashBytes
          : msg;
      const trans = signer.signMessage(vote_msg)
          .then(async res => {
              const chainId = await ethereum.request({ method: "eth_chainId" });
              const currentChainId = parseInt(chainId, 16);
              const token = networkInfo.symbols[currentChainId] ?? 'Unknown';
              const nonce_data = {
                  action: "mrv_generate_nonce",
                  sender_account: account,
                  nonce: wallets_data.nonce,
                  ticker: token,
              };
              jQuery.ajax({
                  type: "post",
                  dataType: "json",
                  url: wallets_data.ajax,
                  data: nonce_data,
                  success: data => {
                      if (data.success) {
                          const user_nonce = data.nonce;
                          const publicAddress = account;
                          const message = `I am signing my one-time nonce: ${user_nonce}`;
                          const hexString = ascii_to_hexa(message);
                          const sign = ethereum.request({
                              method: "personal_sign",
                              params: [hexString, publicAddress, "Example password"],
                          });
                          sign.then(result => {
                              const request_data = {
                                  action: "mrv_save_votes",
                                  nonce: wallets_data.nonce,
                                  user_sign: result,
                                  ticker: token,
                                  signature: res,
                                  sender_account: account,
                                  ticker: "ETH",
                                  vote_type: VoteType,
                                  wallet_type: wallet_type,
                                  balance: balance,
                                  current_url: wallets_data.current_url,
                                  ListID: ListID,
                                  ItemName: ItemName,
                              };
                              jQuery.ajax({
                                  type: "post",
                                  dataType: "json",
                                  url: wallets_data.ajax,
                                  data: request_data,
                                  success: data => {
                                      if (data.status === "success") {
                                          if (data.data.votes === "updated") {
                                              mrv_alert_msg("Voted Already", "success", false);
                                          } else {
                                              let vote = data.data.votes;
                                              let id = data.data.id;
                                              jQuery.each(id, (key, val) => {
                                                  let votes = val.votes === "0" ? "" : val.votes;
                                                  jQuery(`#mrv_total_votes_${val.ids}`).html(
                                                      votes > 0 ? `+${votes}` : votes
                                                  );
                                              });
                                              mrv_alert_msg("Voted Successfully", "success", false);
                                              setTimeout(() => {
                                                  location.reload();
                                              }, 100);
                                          }
                                      }
                                  },
                                  error: (XMLHttpRequest, textStatus, errorThrown) => {
                                      console.log(`Status: ${textStatus} Error: ${errorThrown}`);
                                  },
                              });
                          });
                      }
                  },
                  error: (XMLHttpRequest, textStatus, errorThrown) => {
                      console.log(`Status: ${textStatus} Error: ${errorThrown}`);
                  },
              });
          })
          .catch(error => {
              if (error.code === "4001") {
                  mrv_alert_msg(error.message, "error", 2000);
              } else if (error.code === "-32602") {
                  mrv_alert_msg(error.message, "error", 10000);
              } else {
                  mrv_alert_msg(error.message, "error", false);
              }
          });
  } catch (erro) {
      console.log(erro);
  }
}
function getCookie(name) {
  const value = `; ${document.cookie}`;
  const parts = value.split(`; ${name}=`);
  if (parts.length === 2) return parts.pop().split(";").shift();
}
function ascii_to_hexa(str) {
  var arr1 = [];
  for (var n = 0, l = str.length; n < l; n++) {
    var hex = Number(str.charCodeAt(n)).toString(16);
    arr1.push(hex);
  }
  return arr1.join("");
}

function mrv_ajx_handler(params) {}

function mrv_get_widnow_size() {
  if (window.innerWidth <= 500) {
    return true;
  } else {
    return false;
  }
}

async function mrv_wallet_connect(wallet_type, id) {
  if (wallets_data.infura_id == undefined || wallets_data.infura_id == "") {
    return;
  }
  let walletConnect = new WalletConnectProvider.default({
    infuraId: wallets_data.infura_id,
    rpc: wallets_data.rpc_urls,
  });
  walletConnect.on("connect", (error) => {
    console.log(error);
  });
  walletConnect.on("disconnect", (error) => {
    console.log(error);
  });
  setTimeout(() => {
    if (id != "wallet_connect") {
      let header = jQuery(
        "#walletconnect-wrapper .walletconnect-modal__header"
      );
      header.find("img").attr("src", wallets_data.wallet_logos[id]);
      header.find("p").html(wallet_type);
    }
    /* if (mrv_get_widnow_size()==false){
        jQuery('#walletconnect-wrapper #walletconnect-qrcode-text').html('Scan QR code with ' + wallet_type +'')
        } */
    jQuery("#walletconnect-wrapper").click(function (params) {
      if (id != "wallet_connect") {
        let header = jQuery(
          "#walletconnect-wrapper .walletconnect-modal__header"
        );
        header.find("img").attr("src", wallets_data.wallet_logos[id]);
        header.find("p").html(wallet_type);
      }
    });
  }, 250);
  setTimeout(() => {
    jQuery("#walletconnect-wrapper svg.walletconnect-qrcode__image").css({
      width: "60%",
    });
  }, 50);

  await walletConnect.enable();
  return walletConnect;
}

function cdbbc_ajax_handler(request_data) {
  jQuery.ajax({
    type: "post",
    dataType: "json",
    url: wallets_data.ajax,
    data: request_data,
    success: function (data) {
      if (data.status == "success") {
        return true;
      } else {
        return false;
      }
    },
    error: function (XMLHttpRequest, textStatus, errorThrown) {
      console.log("Status: " + textStatus + "Error: " + errorThrown);
    },
  });
}

function mrv_alert_msg(msg, icons = false, time) {
  Swal.close();
  Swal.fire({
    title: msg,
    icon: icons,
    timer: time,
  });
}