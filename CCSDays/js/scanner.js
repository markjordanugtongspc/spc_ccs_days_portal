document.addEventListener("DOMContentLoaded", function () {
  const startScannerBtn = document.getElementById("startScanner");
  const stopScannerBtn = document.getElementById("stopScanner");
  const scanResult = document.getElementById("scanResult");
  let html5QrCode;

  // Scanner state persistence
  const SCANNER_STATE_KEY = "qrScannerActive";

  // Event selection variables
  const eventSelection = document.getElementById("eventSelection");
  const selectedEventInfo = document.getElementById("selectedEventInfo");
  const selectedEventNameSpan = document.getElementById("selectedEventName");
  let selectedEventId = null;
  let selectedEventName = "";

  // Function to save scanner state to localStorage
  function saveActiveScannerState(isActive) {
    try {
      localStorage.setItem(SCANNER_STATE_KEY, isActive ? "active" : "");
    } catch (e) {
      console.warn("Could not save scanner state:", e);
    }
  }

  // Function to check if scanner should be auto-started
  function shouldAutoStartScanner() {
    try {
      return localStorage.getItem(SCANNER_STATE_KEY) === "active";
    } catch (e) {
      return false;
    }
  }

  // SweetAlert2 custom functions with dark theme
  const Toast = Swal.mixin({
    toast: true,
    position: "top-end",
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    background: '#1a1a1a', // dark-2 equivalent
    color: '#ffffff', // light text
    didOpen: (toast) => {
      toast.addEventListener("mouseenter", Swal.stopTimer);
      toast.addEventListener("mouseleave", Swal.resumeTimer);
    },
    customClass: {
      popup: 'colored-toast',
      timerProgressBar: 'timer-progress'
    }
  });

  // Add custom styles for SweetAlert2
  const style = document.createElement('style');
  style.textContent = `
    .colored-toast {
      border: 1px solid rgba(107, 114, 128, 0.1) !important;
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
    }
    .colored-toast.swal2-icon-success {
      border-left: 4px solid rgb(13, 148, 136) !important;
    }
    .colored-toast.swal2-icon-error {
      border-left: 4px solid rgb(239, 68, 68) !important;
    }
    .colored-toast .swal2-success {
      border-color: rgb(13, 148, 136) !important;
      color: rgb(13, 148, 136) !important;
    }
    .colored-toast .swal2-error {
      border-color: rgb(239, 68, 68) !important;
      color: rgb(239, 68, 68) !important;
    }
    .timer-progress {
      background: rgb(13, 148, 136) !important;
    }
  `;
  document.head.appendChild(style);

  function showSuccessAlert(message) {
    Toast.fire({
      icon: "success",
      title: message,
    });
  }

  function showErrorAlert(title, message) {
    Toast.fire({
      icon: "error",
      title: title,
      text: message,
      iconColor: 'rgb(239, 68, 68)', // Red color for error icon
    });
  }

  // Flag to track if we're currently processing a scan/submission
  let isProcessing = false;

  // Function to update Recent Activity section
  async function updateRecentActivity() {
    try {
      const response = await fetch("../includes/api/get_recent_activity.php");
      const data = await response.json();

      if (data.success && data.entries) {
        const activityContainer = document.querySelector(
          ".bg-dark-2.rounded-lg.overflow-hidden > div:last-child"
        );

        if (activityContainer) {
          // Clear existing entries
          activityContainer.innerHTML = "";

          // Auto-sign student IDs (these students get auto attendance)
          const autoSignStudents = ['2022-00752', '2022-00769', '2021-01066', '2022-00008', '2022-01308'];

          // Add new entries
          data.entries.forEach((entry) => {
            const timeIn = new Date(entry.Sign_In_Time).toLocaleTimeString([], {
              hour: "2-digit",
              minute: "2-digit",
            });

            // Logic for determining type:
            // 1. If student is in auto-sign list OR has QR_Code format (contains '-'), it's "Auto"
            // 2. Otherwise it's "Manual" (manual entry)
            const isAutoSign = autoSignStudents.includes(entry.Student_ID) || 
                              (entry.QR_Code && entry.QR_Code.includes('-'));
            const typeClass = isAutoSign ? 'bg-blue-900 text-blue-300' : 'bg-gray-700 text-gray-300';
            const typeLabel = isAutoSign ? 'Auto' : 'Manual';

            const entryElement = document.createElement("div");
            entryElement.className = "grid grid-cols-7 gap-4 p-4";
            entryElement.innerHTML = `
                        <div>${entry.Student_ID}</div>
                        <div>${entry.Name}</div>
                        <div>${entry.Year}</div>
                        <div>${entry.Event_Name || "N/A"}</div>
                        <div><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-900 text-green-300">Sign In</span></div>
                        <div>${timeIn}</div>
                        <div><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${typeClass}">${typeLabel}</span></div>
                    `;

            activityContainer.appendChild(entryElement);

            if (entry.Sign_Out_Time) {
              const timeOut = new Date(entry.Sign_Out_Time).toLocaleTimeString(
                [],
                {
                  hour: "2-digit",
                  minute: "2-digit",
                }
              );

              const signOutElement = document.createElement("div");
              signOutElement.className = "grid grid-cols-7 gap-4 p-4";
              signOutElement.innerHTML = `
                            <div>${entry.Student_ID}</div>
                            <div>${entry.Name}</div>
                            <div>${entry.Year}</div>
                            <div>${entry.Event_Name || "N/A"}</div>
                            <div><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-900 text-red-300">Sign Out</span></div>
                            <div>${timeOut}</div>
                            <div><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${typeClass}">${typeLabel}</span></div>
                        `;

              activityContainer.appendChild(signOutElement);
            }
          });
        }
      }
    } catch (error) {
      console.error("Error updating recent activity:", error);
    }
  }

  // Event selection handler
  if (eventSelection) {
    eventSelection.addEventListener("change", function () {
      selectedEventId = this.value;
      const selectedOption = this.options[this.selectedIndex];
      selectedEventName = selectedOption.text;

      // Save selected event to sessionStorage
      sessionStorage.setItem("selectedEventId", selectedEventId);
      sessionStorage.setItem("selectedEventName", selectedEventName);

      if (selectedEventId) {
        selectedEventNameSpan.textContent = selectedEventName;
        selectedEventInfo.classList.remove("hidden");

        // Enable scanner buttons if event is selected
        startScannerBtn.disabled = false;

        // If scanner was already running, restart it with the new event context
        if (html5QrCode && html5QrCode.isScanning) {
          stopScanner();
          setTimeout(() => startScanner(), 500);
        }
      } else {
        selectedEventInfo.classList.add("hidden");
        startScannerBtn.disabled = true;

        // Stop scanner if no event is selected
        if (html5QrCode && html5QrCode.isScanning) {
          stopScanner();
        }

        // Remove saved event from sessionStorage
        sessionStorage.removeItem("selectedEventId");
        sessionStorage.removeItem("selectedEventName");
      }
    });

    // Restore selected event from sessionStorage on page load
    const savedEventId = sessionStorage.getItem("selectedEventId");
    const savedEventName = sessionStorage.getItem("selectedEventName");

    if (savedEventId && savedEventName) {
      eventSelection.value = savedEventId;
      selectedEventId = savedEventId;
      selectedEventName = savedEventName;
      selectedEventNameSpan.textContent = savedEventName;
      selectedEventInfo.classList.remove("hidden");
      startScannerBtn.disabled = false;
    }
  }

  // Disable scanner buttons initially until event is selected
  startScannerBtn.disabled = true;

  // Render latest person card inside Manual Entry panel
  async function showLatestPerson(studentId, statusLabel) {
    try {
      const resp = await fetch(
        `../includes/api/fetch_student_details.php?id=${encodeURIComponent(
          studentId
        )}`
      );
      const student = await resp.json();
      if (!student || student.error) return;

      const manualContent = document.querySelector(".manual-content");
      if (!manualContent) return;

      let card = document.getElementById("latestPersonCard");
      if (!card) {
        card = document.createElement("div");
        card.id = "latestPersonCard";
        card.className = "bg-dark-2 rounded-lg p-6 mb-6";
        manualContent.insertBefore(card, manualContent.firstChild);
      }
      const statusClass =
        statusLabel === "Sign Out"
          ? "bg-red-900 text-red-300"
          : "bg-green-900 text-green-300";
      const initials = (() => {
        const parts = (student.Name || "").split(" ");
        return (
          (parts[0]?.[0] || "") +
          (parts.length > 1 ? parts[parts.length - 1][0] : "")
        );
      })();
      const nowTime = new Date().toLocaleTimeString([], {
        hour: "2-digit",
        minute: "2-digit",
      });

      card.innerHTML = `
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-start">
                        <div class="h-12 w-12 rounded-full bg-teal-900/30 flex items-center justify-center text-teal-light font-semibold">${initials}</div>
                        <div class="ml-4">
                            <div class="text-lg font-medium text-light">${
                              student.Name || "Unknown"
                            }</div>
                            <div class="text-gray-400 text-sm">${
                              student.Student_ID || studentId
                            }</div>
                        </div>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusClass}">${statusLabel}</span>
                </div>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <div class="text-gray-400">Course</div>
                        <div class="text-light">${
                          student.College || "CCS"
                        }</div>
                    </div>
                    <div>
                        <div class="text-gray-400">Year</div>
                        <div class="text-light">${
                          student.Year ? `${student.Year} Year` : "N/A"
                        }</div>
                    </div>
                    <div>
                        <div class="text-gray-400">Gender</div>
                        <div class="text-light">${
                          student.Gender === "M"
                            ? "Male"
                            : student.Gender === "F"
                            ? "Female"
                            : "N/A"
                        }</div>
                    </div>
                    <div>
                        <div class="text-gray-400">Time</div>
                        <div class="text-light">${nowTime}</div>
                    </div>
                </div>
            `;
    } catch (_) {
      // ignore rendering errors
    }
  }

  // After reload, show the latest person if stored
  (function renderLastFromSession() {
    const lastId = sessionStorage.getItem("lastPersonId");
    const lastStatus = sessionStorage.getItem("lastPersonStatus");
    if (lastId && lastStatus) {
      showLatestPerson(lastId, lastStatus);
      // keep it for this session unless next update overrides
    }
  })();

  // Tab switching functionality
  const tabItems = document.querySelectorAll(".tab-item");
  const scannerTab = document.querySelector(".scanner-content");
  const manualTab = document.querySelector(".manual-content");

  tabItems.forEach((tab, index) => {
    tab.addEventListener("click", function () {
      // Remove active class from all tabs
      tabItems.forEach((item) => item.classList.remove("active"));
      // Add active class to clicked tab
      this.classList.add("active");

      // Toggle content visibility based on selected tab
      if (this.textContent.trim() === "QR Scanner") {
        scannerTab.classList.remove("hidden");
        manualTab.classList.add("hidden");
        scannerTab.classList.add("fade-in");
        setTimeout(() => {
          scannerTab.classList.remove("fade-in");
        }, 500);
      } else if (this.textContent.trim() === "Manual Entry") {
        scannerTab.classList.add("hidden");
        manualTab.classList.remove("hidden");
        manualTab.classList.add("fade-in");
        setTimeout(() => {
          manualTab.classList.remove("fade-in");
          document.getElementById("bulkStudentId").focus();
        }, 500);
      }
    });
  });

  // Utility to display QR scan results and errors
  function showScanMessage(message, isError = false) {
    // Clear any previous error messages
    const prevError = document.getElementById("qrError");
    if (prevError) prevError.remove();

    if (isError) {
      showNotification(message, "error");
      console.error("QR scan error:", message);

      // Display inline error message
      const errorDiv = document.createElement("div");
      errorDiv.id = "qrError";
      errorDiv.className = "text-red-500 mt-2";
      errorDiv.textContent = message;
      scanResult.innerHTML = "";
      scanResult.appendChild(errorDiv);
    } else {
      // For success messages, directly update the scan result content
      // and clear any existing error spans
      scanResult.innerHTML = "";
      const resultText = document.createElement("span");
      resultText.className = "text-light";
      resultText.textContent = message;
      scanResult.appendChild(resultText);
    }
  }

  // Function to handle the QR code scanning
  function handleQRCodeScan(decodedText) {
    // Check if we're already processing a scan
    if (isProcessing) {
      console.log("Already processing a scan, ignoring this one");
      return;
    }

    // Set processing flag
    isProcessing = true;

    // Show the decoded text in the scan result area
    showScanMessage(decodedText);

    // Send the scanned QR code to the backend API with event ID
    fetch("../includes/api/attendance_handler.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body:
        "qr_code=" +
        encodeURIComponent(decodedText) +
        "&event_id=" +
        encodeURIComponent(selectedEventId),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          showSuccessAlert(data.message);
          
          // Play success sound if available
          if (typeof window.playSuccessAudio === "function") {
            try {
              window.playSuccessAudio();
            } catch (_) {}
          }
          
          // Show latest person
          const isSignOut = data.message.toLowerCase().includes("signed out");
          const statusLabel = isSignOut ? "Sign Out" : "Sign In";
          showLatestPerson(decodedText, statusLabel);
          
          // Update the recent activity section
          updateRecentActivity();
        } else {
          showErrorAlert("Error", data.message);
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        showErrorAlert("Error", "Failed to process QR code");
      })
      .finally(() => {
        // Reset processing flag after a delay to prevent rapid successive scans
        setTimeout(() => {
          isProcessing = false;
        }, 1500);
      });
  }

  // Start the scanner
  function startScanner() {
    // Check if event is selected
    if (!selectedEventId) {
      showScanMessage("Please select an event first", true);
      return;
    }
    
    // Check if scanner is already initialized
    if (!html5QrCode) {
      try {
        html5QrCode = new Html5Qrcode("reader");
        console.log("QR scanner initialized on demand");
      } catch (err) {
        showScanMessage(`Failed to initialize scanner: ${err}`, true);
        return;
      }
    }

    html5QrCode
      .start(
        { facingMode: "environment" },
        { fps: 10, qrbox: { width: 250, height: 250 } },
        handleQRCodeScan,
        () => {} // Ignore verbose logs
      )
      .then(() => {
        startScannerBtn.disabled = true;
        stopScannerBtn.disabled = false;
        scanResult.innerHTML = `
          <div class="text-center">
            <div class="text-teal-light font-medium mb-1">Scanning for: ${selectedEventName}</div>
            <span class="text-gray-500">Camera active. Scan a QR code...</span>
          </div>
        `;
        saveActiveScannerState(true);
      })
      .catch((err) => {
        console.error("Error starting scanner:", err);
        // Provide more user-friendly error messages
        let errorMsg = `Unable to start scanner: ${err}`;
        if (err.toString().includes("NotFoundError")) {
          errorMsg = "No camera found. Please connect a camera and try again.";
        } else if (err.toString().includes("NotAllowedError")) {
          errorMsg = "Camera access denied. Please allow camera access and try again.";
        } else if (err.toString().includes("NotReadableError")) {
          errorMsg = "Camera is in use by another application. Please close other apps using the camera.";
        }
        showScanMessage(errorMsg, true);
      });
  }

  // Stop the scanner
  function stopScanner() {
    if (!html5QrCode) {
      console.warn("QR scanner not initialized");
      return;
    }

    // Save scanner state as inactive
    saveActiveScannerState(false);

    html5QrCode
      .stop()
      .then(() => {
        startScannerBtn.disabled = false;
        stopScannerBtn.disabled = true;
        scanResult.innerHTML = '<span class="text-gray-500">Scanner stopped</span>';
      })
      .catch((err) => {
        console.error("Error stopping scanner:", err);
        showScanMessage("Unable to stop scanner: " + err, true);
      });
  }

  // Initialize the scanner
  function initScanner() {
    try {
      html5QrCode = new Html5Qrcode("reader");
      console.log("QR scanner initialized successfully");

      // Auto-start scanner if previously active
      if (shouldAutoStartScanner()) {
        console.log("Auto-starting scanner based on saved preference");
        startScanner();
      }
    } catch (error) {
      console.error("Error initializing QR scanner:", error);
    }
  }

  // Start button event listener
  startScannerBtn.addEventListener("click", function() {
    startScanner();
  });

  // Stop button event listener
  stopScannerBtn.addEventListener("click", function() {
    stopScanner();
  });

  // Manual sign in/out buttons
  const signInBtn = document.getElementById("signInBtn");
  const signOutBtn = document.getElementById("signOutBtn");
  const studentIdInput = document.getElementById("studentId");

  // Add event listener for Enter key on the student ID input
  studentIdInput.addEventListener("keydown", function (e) {
    if (e.key === "Enter") {
      // Perform sign in by default when Enter is pressed
      signInBtn.click();
    }
  });

  // Manual Sign In Button Click Handler Start
  signInBtn.addEventListener("click", async function () {
    // Check if event is selected
    if (!selectedEventId) {
      showNotification("Please select an event first", "error");
      return;
    }

    const studentId = studentIdInput.value.trim();
    if (!studentId) {
      studentIdInput.classList.add("border-red-500");
      showNotification("Please enter a student ID", "error");
      setTimeout(() => studentIdInput.classList.remove("border-red-500"), 2000);
      return;
    }

    // Check if we're already processing a request
    if (isProcessing) {
      showNotification(
        "Please wait before submitting again (cooldown period)",
        "error"
      );
      return;
    }

    // Set processing flag
    isProcessing = true;
    // Manual Sign In API call Start
    fetch("../includes/api/attendance_handler.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body:
        "qr_code=" +
        encodeURIComponent(studentId) +
        "&event_id=" +
        encodeURIComponent(selectedEventId),
    })
      .then((res) => {
        if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
        return res.json();
      })
      .then(async (data) => {
        if (data.success) {
          if (typeof window.playSuccessAudio === "function") {
            try {
              window.playSuccessAudio();
            } catch (_) {}
          }

          // Instead of redirecting, update the UI directly
          showLatestPerson(studentId, "Sign In");

          // Update recent activity after successful manual sign-in
          updateRecentActivity();

          // Reset processing flag after cooldown period
          setTimeout(() => {
            isProcessing = false;
          }, 1500); // 1.5 second cooldown
        } else {
          showNotification(data.message, "error");
          isProcessing = false; // Reset immediately if error
        }
      })
      .catch((err) => showNotification("Error: " + err.message, "error"));
    // Manual Sign In API call End

    studentIdInput.value = "";
    studentIdInput.focus();
  });
  // Manual Sign In Button Click Handler End

  // Manual Sign Out Button Click Handler Start
  signOutBtn.addEventListener("click", async function () {
    // Check if event is selected
    if (!selectedEventId) {
      showNotification("Please select an event first", "error");
      return;
    }

    const studentId = studentIdInput.value.trim();
    if (!studentId) {
      studentIdInput.classList.add("border-red-500");
      showNotification("Please enter a student ID", "error");
      setTimeout(() => studentIdInput.classList.remove("border-red-500"), 2000);
      return;
    }

    // Check if we're already processing a request
    if (isProcessing) {
      showNotification(
        "Please wait before submitting again (cooldown period)",
        "error"
      );
      return;
    }

    // Set processing flag
    isProcessing = true;
    // Manual Sign Out API call Start
    fetch("../includes/api/attendance_handler.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body:
        "qr_code=" +
        encodeURIComponent(studentId) +
        "&event_id=" +
        encodeURIComponent(selectedEventId),
    })
      .then((res) => {
        if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
        return res.json();
      })
      .then(async (data) => {
        if (data.success) {
          if (typeof window.playSuccessAudio === "function") {
            try {
              window.playSuccessAudio();
            } catch (_) {}
          }

          // Instead of redirecting, update the UI directly
          showLatestPerson(studentId, "Sign Out");

          // Update recent activity after successful manual sign-out
          updateRecentActivity();

          // Reset processing flag after cooldown period
          setTimeout(() => {
            isProcessing = false;
          }, 1500); // 1.5 second cooldown
        } else {
          showNotification(data.message, "error");
          isProcessing = false; // Reset immediately if error
        }
      })
      .catch((err) => showNotification("Error: " + err.message, "error"));
    // Manual Sign Out API call End

    studentIdInput.value = "";
    studentIdInput.focus();
  });
  // Manual Sign Out Button Click Handler End

  // Notification function (smaller, less intrusive than modal)
  function showNotification(message, type = "success") {
    const notification = document.createElement("div");
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
            <div class="notification-icon">
                ${
                  type === "success"
                    ? `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>`
                    : `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>`
                }
            </div>
            <div class="notification-message">${message}</div>
        `;

    document.body.appendChild(notification);

    // Slide in animation
    setTimeout(() => {
      notification.classList.add("show");
    }, 10);

    // Auto-remove after 3 seconds
    setTimeout(() => {
      notification.classList.remove("show");
      setTimeout(() => {
        if (document.body.contains(notification)) {
          document.body.removeChild(notification);
        }
      }, 300); // Wait for the slide out animation
    }, 3000);
  }

  // Initialize the scanner when the page loads
  setTimeout(initScanner, 500);
});