async function handleClick(e) {
  e.preventDefault();
  setElementTextAndDisable(e.currentTarget, "ACTIVATING...");
  await activate_plugin();
}

async function setElementTextAndDisable(element, text) {
  element.textContent = text;
  element.setAttribute("disabled", true);
}

async function activate_plugin() {
  const noticeEl = document.getElementById("messager");
  const defaultMsg = noticeEl.textContent;
  const activateBtn = document.getElementById("meta-plugin-activate-btn");
  const tosBox = document.getElementById("accept_tos");

  if (!tosBox.checked) {
    displayErrorAndReset(noticeEl, defaultMsg, activateBtn, metaRanker.tosRequired);
    return;
  }

  try {
    const newAccounts = await ethereum.request({ method: "eth_requestAccounts" });
    handleNewAccounts(newAccounts, noticeEl, defaultMsg, activateBtn);
  } catch (error) {
    console.log("Error requesting Ethereum accounts.");
  }
}

async function displayErrorAndReset(noticeEl, defaultMsg, activateBtn, errorMessage) {
  updateNoticeAndBtn(noticeEl, activateBtn, "err", errorMessage, "ACTIVATE");
  setTimeout(() => {
    noticeEl.textContent = defaultMsg;
    noticeEl.classList.remove("err");
  }, 3000);
}

async function handleNewAccounts(newAccounts, noticeEl, defaultMsg, activateBtn) {
  if (newAccounts.length > 0) {
    const walletAddress = newAccounts[0];
    try {
      const chainId = await ethereum.request({ method: "eth_chainId" });
      const currentChainId = parseInt(chainId, 16);
      const token = networkInfo.symbols[currentChainId] ?? 'Unknown';
      handleChainId(currentChainId, walletAddress, token, noticeEl, defaultMsg, activateBtn);
    } catch (error) {
      updateNoticeAndBtn(noticeEl, activateBtn, "err", "Error getting the current Ethereum chainId. Please switch to mainnet.", "ACTIVATE");
      console.log("Error getting the current Ethereum chainId.");
    }
  } else {
    console.log("No Ethereum accounts found.");
  }
}

async function handleChainId(currentChainId, walletAddress, token, noticeEl, defaultMsg, activateBtn) {
  if (!networkInfo.testnets.includes(currentChainId)) {
    await activateOnMainnet(walletAddress, token, noticeEl, activateBtn);
  } else {
    handleTestnet(noticeEl, defaultMsg, activateBtn);
  }
}

async function activateOnMainnet(walletAddress, token, noticeEl, activateBtn) {
  try {
    const response = await fetch(ajaxurl, {
      method: "POST",
      body: new URLSearchParams({
        wallet: walletAddress,
        plugin: "meta-ranker",
        ticker: token,
        action: "metaranker_activate_site",
      }),
    });
    const result = await response.json();
    if (result.success) {
      updateNoticeAndBtn(noticeEl, activateBtn, "ok", result.message, "ACTIVATED");
      setTimeout(() => (window.location.href = metaRanker.adminURL), 3000);
    } else {
      updateNoticeAndBtn(noticeEl, activateBtn, "err", result.message, "ACTIVATE");
    }
  } catch (err) {
    console.log(err);
  }
}

async function handleTestnet(noticeEl, defaultMsg, activateBtn) {
  updateNoticeAndBtn(noticeEl, activateBtn, "err", "Please switch to mainnet.", "ACTIVATE");
  try {
    await ethereum.request({
      method: "wallet_switchEthereumChain",
      params: [{ chainId: "0x1" }],
    });
    noticeEl.classList.remove("err");
    noticeEl.textContent = defaultMsg;
    activateBtn.textContent = "ACTIVATE";
    activate_plugin();
  } catch (error) {
    console.log(error);
  }
}

function updateNoticeAndBtn(noticeEl, activateBtn, className, noticeText, btnText) {
  noticeEl.classList.add(className);
  noticeEl.textContent = noticeText;
  activateBtn.textContent = btnText;
  if (btnText === "ACTIVATE") {
    activateBtn.removeAttribute("disabled");
  }
}

window.addEventListener("DOMContentLoaded", () => {
  const activateBtn = document.getElementById("meta-plugin-activate-btn");
  if (activateBtn) {
    activateBtn.removeEventListener("click", handleClick);
    activateBtn.addEventListener("click", handleClick);
  }
});
