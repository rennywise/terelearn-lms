const functions = require('firebase-functions');
const admin     = require('firebase-admin');
admin.initializeApp();

const db = admin.firestore();

/**
 * Log SMS verifications from the frontend.
 * Firebase Phone Auth handles the actual SMS delivery for free.
 * This function just records the event to Firestore.
 */
exports.logSMSVerification = functions.https.onCall(async (data, context) => {
  if (!context.auth) {
    throw new functions.https.HttpsError('unauthenticated', 'Must be logged in');
  }
  const { phone } = data;
  await db.collection('smsLogs').add({
    uid:       context.auth.uid,
    phone:     phone || 'unknown',
    verified:  true,
    timestamp: admin.firestore.FieldValue.serverTimestamp()
  });
  return { success: true };
});

/**
 * Auto-cleanup: delete expired OTP/session logs older than 1 hour.
 * Runs every hour via Cloud Scheduler.
 */
exports.cleanupLogs = functions.pubsub.schedule('every 60 minutes').onRun(async () => {
  const cutoff = new Date(Date.now() - 60 * 60 * 1000);
  const snap   = await db.collection('smsLogs').where('timestamp', '<', cutoff).get();
  const batch  = db.batch();
  snap.forEach(doc => batch.delete(doc.ref));
  await batch.commit();
  console.log(`Cleaned up ${snap.size} old log entries`);
  return null;
});