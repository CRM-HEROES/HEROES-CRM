
<html>

	<head>
		<title>Registration</title>
	</head>

	<body style="
		margin: 0;
		padding: 50px;
		background-color: #F5F5F5;
		font-family: helvetica;
	">
		<!-- Container -->
		<div style="
			margin: auto;
			width: 400px;
			border-radius: 16px;
			background-color: #FFFFFF;
			height: auto;
		">
			<!-- Header -->
			<div style="
			    width: 100%;
			    height: 80px;
			    padding-top: 28px;
			    position: relative;
			    overflow: hidden;
			    text-align: center;
				background-image: url('{{ url('images/email/header-bg.png') }}');
    			background-size: 100% 100%;
			">
				<!-- Header background -->
				<!--div style="
				    position: absolute;
				    background-color: #F5FBFD;
				    width: 170%;
				    height: 200%;
				    bottom: 0;
				    left: 50%;
				    transform: translateX(-50%);
				    border-radius: 50%;
				"></div-->

				<img style="
					position: relative;
				"
				src="{{ url('images/logo.png') }}" />
			</div>

			<!-- Bg -->
			<div style="
				width: 100%;
				height: 180px;
				background-image: url('{{ url('images/email/bg.png') }}');
    			background-size: contain;
			"></div>

			<!-- body -->
			<div style="
				width: 100%;
				height: auto;
			">
				<div style="
					width: 100%;
					height: auto;
					text-align: center;
					padding-top: 16px;
				">
					<img style="
						width: 25px
					"
					src="{{ url('images/email/hi.png') }}" />
					<br>
					<span style="
						font-size: 18px;
						font-weight: bold;
						display: inline-block;
						margin-top: 16px;
					">Bonjour {{ $user->name }}</span>
					<br>
					<span style="
					    font-size: 14px;
					    text-align: center;
					    display: inline-block;
					    margin-top: 24px;
					    line-height: 26px;
					    width: 100%;
					    padding: 0 30px;
					    box-sizing: border-box;
					">On vient de vous inscrire sur HeroesCRM.
Pour confirmer votre inscription et paramétrer votre compte, veuillez accéder au lien suivant:</span>
				</div>
			</div>

			<!-- Footer -->
			<div style="
				width: 100%;
				height: auto;
			">
				<div style="
					width: 100%;
					height: auto;
					box-sizing: border-box;
					padding: 40px 10px;
					text-align: center;
				">
					<a href="{{ $url }}" style="
						padding: 12px 16px;
						background-color: #49BDE1;
						color: white;
						font-size: 15px;
						border-radius: 8px;
					">S'inscrire</a>
				</div>
			</div>
		</div>
	</body>
</html>