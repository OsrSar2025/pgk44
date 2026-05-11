export async function onRequest(context) {
  const { searchParams } = new URL(context.request.url);
  // รับชื่อตารางจาก URL เช่น ?table=user
  const table = searchParams.get('table') || 'index1'; 

  try {
    // ใช้กุญแจ env.DB ตัวเดิม ไขเข้าบ้านเดิม แต่เปลี่ยนห้องตามที่สั่ง
    const { results } = await context.env.DB.prepare(
      `SELECT * FROM ${table} LIMIT 100`
    ).all();

    return new Response(JSON.stringify(results), {
      headers: { 
        "Content-Type": "application/json",
        "Access-Control-Allow-Origin": "*" 
      },
    });
  } catch (err) {
    return new Response(JSON.stringify({ error: err.message }), { status: 500 });
  }
}
