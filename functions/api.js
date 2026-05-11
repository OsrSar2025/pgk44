export async function onRequest(context) {
  // ดึงข้อมูลจากตาราง index1 ที่มึงรันไว้ตะกี้
  const { results } = await context.env.DB.prepare(
    "SELECT * FROM index1"
  ).all();

  return new Response(JSON.stringify(results), {
    headers: { 
      "Content-Type": "application/json",
      "Access-Control-Allow-Origin": "*" 
    },
  });
}
