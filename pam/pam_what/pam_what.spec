# $Revision: 1.9 $, $Date: 2004/10/06 16:03:09 $
Summary:	PAM Modules to postgres connection
Summary(fr):	Module PAM pour la connection à une base postgres
Name:		pam_what
Version:	0.4.0
Release:	1
License:	GPL or BSD
Group:		Base
Source0:	ftp://ftp.souillac.anakeen.com/pub/anakeen/%{name}-%{version}.tar.gz
Vendor:         Anakeen           
URL:		http://www.anakeen.com
#BuildRequires:	pam-devel
#Requires:	make
Requires:	pam >= 0.72
Requires:	postgresql-libs >= 7.2
Provides:	pam_what.so
BuildRoot:	%{_tmppath}/%{name}-%{version}-root-%(id -u -n)
Conflicts:	WHAT < 0.3.0
BuildArchitectures: i686

%description
This PAM module is used to authent user with the WHAT database.
Only authent & account modules are provided

%description -l fr
Ce module PAM permet l'authentification d'utilisateur via la base de données de WHAT
Seuls les services d'authenfication et de compte sont fournis


%prep
%setup -q -n %{name}-%{version}


%build

%configure \
	--with-postgres --bindir="/lib/security"
%{__make}

%install
rm -rf $RPM_BUILD_ROOT
install -d $RPM_BUILD_ROOT/lib/security

%{__make} install DESTDIR=$RPM_BUILD_ROOT


%post   
%postun 

%clean
rm -rf $RPM_BUILD_ROOT

%files
%defattr(644,root,root,755)
%attr(0755,root,root) /lib/security/pam_what.so




%changelog
* Fri Jul 06 2001 Eric Brison <eric.brison@anakeen.com>
- Build first RPM


$Log: pam_what.spec,v $
Revision 1.9  2004/10/06 16:03:09  eric
Add only [activate|expire] option

Revision 1.8  2003/10/21 09:39:28  eric
compatible WHAT < 0.3.0 sans expire

Revision 1.7  2003/08/12 13:42:00  eric
prise en compte de l'expiration dans account

Revision 1.6  2002/08/06 11:38:11  eric
suppression require WHAT

Revision 1.5  2002/02/27 11:10:10  yannick
Prise en compte Postgresql 7.2

Revision 1.4  2002/01/09 08:43:49  eric
change to new package WHAT

Revision 1.3  2001/09/12 09:12:51  eric
all syslog are writen for LOG_DEBUG

Revision 1.2  2001/08/21 12:58:55  eric
correction fuite memoire

Revision 1.1  2001/07/31 08:26:21  eric
first

