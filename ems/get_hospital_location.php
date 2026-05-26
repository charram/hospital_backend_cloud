TextButton(
  onPressed: () async {
    try {
      final uri = Uri.parse(
        "${AppConfig.apiBaseUrl}ems/accept_job.php",
      );

      final res = await http.post(uri, body: {
        "emergency_id": jobId,
      });

      debugPrint("ACCEPT STATUS: ${res.statusCode}");
      debugPrint("ACCEPT BODY: ${res.body}");

      // 🔥 🔥 🔥 เพิ่มตรงนี้
      final pref = await SharedPreferences.getInstance();
      final emsId = pref.getInt("ems_id") ?? 0;

      await pref.setInt("ems_session_id", int.parse(jobId));
      await pref.setInt("ems_id", emsId);

      debugPrint("🔥 SAVE session=$jobId ems=$emsId");

      // เริ่ม GPS
      await _startSendingGps(jobId);

      await _stopAlertSound();
    } catch (e) {
      debugPrint("❌ ACCEPT ERROR: $e");
    }

    if (!context.mounted) return;

    Navigator.pop(context);
    Navigator.pushNamed(context, "/ems-tracking");
  },
  child: const Text("รับงาน"),
)