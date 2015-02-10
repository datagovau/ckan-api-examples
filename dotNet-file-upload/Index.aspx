<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="Index.aspx.cs" Inherits="DataGovAuUpload.Index" %>

<!doctype html>
<html class="no-js" lang="en">
<head >
   <title>Upload to data.gov.au</title>
   <link href="/Styles/Site.css" rel="stylesheet" type="text/css" />
</head>
<body>
   <div class="header">
      <div class="title">
         <h1>Upload to data.gov.au </h1>
      </div>
      <div class="clear">
      </div>
   </div>
   <form id="Form1" runat="server">
      <h2>Parameters </h2>
      <p>
         <asp:Label ID="Label1" runat="server" AssociatedControlID="TextBoxFileName">Filename:</asp:Label><br />
         <input type="file" size="75" id="TextBoxFileName" runat="server" maxlength="1024" />
      </p>
      <p>
         <asp:Label ID="Label2" runat="server" AssociatedControlID="TextBoxResourceId">Resource Id:</asp:Label><br />
         <asp:TextBox ID="TextBoxResourceId" Columns="90" MaxLength="1024" runat="server" CssClass="textEntry" />
      </p>
      <p>
         <asp:Label ID="Label3" runat="server" AssociatedControlID="TextBoxApiKey">Api key:</asp:Label><br />
         <asp:TextBox ID="TextBoxApiKey" Columns="90" MaxLength="1024" runat="server" CssClass="textEntry" />
      </p>
      <p>
         <asp:Button ID="SubmitButton" Text="Upload" runat="server" />
      </p>
      <h2>Result</h2>
      <asp:PlaceHolder ID="SuccessPlace" runat="server" Visible="false">
         <p class="successNotification">File sucessfully uploaded</p>
      </asp:PlaceHolder>
      <asp:PlaceHolder ID="ErrorPlace" runat="server" Visible="false">
         <p class="failureNotification">File uploaded failed</p>
      </asp:PlaceHolder>
      <p>
      <asp:TextBox ID="TextBoxOutcome" runat="server" Rows="15" Columns="80" TextMode="MultiLine" ReadOnly="true" AutoPostBack="false" />
      </p>
   </form>
</body>
</html>
